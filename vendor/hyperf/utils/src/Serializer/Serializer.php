<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Utils\Serializer;

use Hyperf\Contract\NormalizerInterface as Normalizer;
use Symfony\Component\Serializer\Encoder;
use Symfony\Component\Serializer\Encoder\ChainDecoder;
use Symfony\Component\Serializer\Encoder\ChainEncoder;
use Symfony\Component\Serializer\Encoder\ContextAwareDecoderInterface;
use Symfony\Component\Serializer\Encoder\ContextAwareEncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Serializer serializes and deserializes data.
 *
 * objects are turned into arrays by normalizers.
 * arrays are turned into various output formats by encoders.
 *
 *     $serializer->serialize($obj, 'xml')
 *     $serializer->decode($data, 'xml')
 *     $serializer->denormalize($data, 'Class', 'xml')
 */
class Serializer implements Normalizer, SerializerInterface, ContextAwareNormalizerInterface, ContextAwareDenormalizerInterface, ContextAwareEncoderInterface, ContextAwareDecoderInterface
{
    private const SCALAR_TYPES = [
        'int' => true,
        'bool' => true,
        'float' => true,
        'string' => true,
    ];

    /**
     * @var Encoder\ChainEncoder
     */
    protected $encoder;

    /**
     * @var Encoder\ChainDecoder
     */
    protected $decoder;

    private $normalizers = [];

    private $denormalizerCache = [];

    private $normalizerCache = [];

    /**
     * @param (NormalizerInterface|DenormalizerInterface|mixed)[] $normalizers
     * @param (EncoderInterface|DecoderInterface|mixed)[] $encoders
     */
    public function __construct(array $normalizers = [], array $encoders = [])
    {
        foreach ($normalizers as $normalizer) {
            if ($normalizer instanceof SerializerAwareInterface) {
                $normalizer->setSerializer($this);
            }

            if ($normalizer instanceof DenormalizerAwareInterface) {
                $normalizer->setDenormalizer($this);
            }

            if ($normalizer instanceof NormalizerAwareInterface) {
                $normalizer->setNormalizer($this);
            }

            if (! ($normalizer instanceof NormalizerInterface || $normalizer instanceof DenormalizerInterface)) {
                throw new InvalidArgumentException(sprintf('The class "%s" neither implements "%s" nor "%s".', get_debug_type($normalizer), NormalizerInterface::class, DenormalizerInterface::class));
            }
        }
        $this->normalizers = $normalizers;

        $decoders = [];
        $realEncoders = [];
        foreach ($encoders as $encoder) {
            if ($encoder instanceof SerializerAwareInterface) {
                $encoder->setSerializer($this);
            }
            if ($encoder instanceof DecoderInterface) {
                $decoders[] = $encoder;
            }
            if ($encoder instanceof EncoderInterface) {
                $realEncoders[] = $encoder;
            }

            if (! ($encoder instanceof EncoderInterface || $encoder instanceof DecoderInterface)) {
                throw new InvalidArgumentException(sprintf('The class "%s" neither implements "%s" nor "%s".', get_debug_type($encoder), EncoderInterface::class, DecoderInterface::class));
            }
        }
        $this->encoder = new ChainEncoder($realEncoders);
        $this->decoder = new ChainDecoder($decoders);
    }

    final public function serialize($data, string $format, array $context = []): string
    {
        if (! $this->supportsEncoding($format, $context)) {
            throw new NotEncodableValueException(sprintf('Serialization for the format "%s" is not supported.', $format));
        }

        if ($this->encoder->needsNormalization($format, $context)) {
            $data = $this->normalize($data, $format, $context);
        }

        return $this->encode($data, $format, $context);
    }

    final public function deserialize($data, string $type, string $format, array $context = [])
    {
        if (! $this->supportsDecoding($format, $context)) {
            throw new NotEncodableValueException(sprintf('Deserialization for the format "%s" is not supported.', $format));
        }

        $data = $this->decode($data, $format, $context);

        return $this->denormalize($data, $type, $format, $context);
    }

    public function normalize($data, string $format = null, array $context = [])
    {
        // If a normalizer supports the given data, use it
        if ($normalizer = $this->getNormalizer($data, $format, $context)) {
            return $normalizer->normalize($data, $format, $context);
        }

        if ($data === null || is_scalar($data)) {
            return $data;
        }

        if (\is_array($data) || $data instanceof \Traversable) {
            if ($data instanceof \Countable && $data->count() === 0) {
                return $data;
            }

            $normalized = [];
            foreach ($data as $key => $val) {
                $normalized[$key] = $this->normalize($val, $format, $context);
            }

            return $normalized;
        }

        if (\is_object($data)) {
            if (! $this->normalizers) {
                throw new LogicException('You must register at least one normalizer to be able to normalize objects.');
            }

            throw new NotNormalizableValueException(sprintf('Could not normalize object of type "%s", no supporting normalizer found.', get_debug_type($data)));
        }

        throw new NotNormalizableValueException('An unexpected value could not be normalized: ' . (! \is_resource($data) ? var_export($data, true) : sprintf('%s resource', get_resource_type($data))));
    }

    /**
     * @param mixed $data
     * @throws NotNormalizableValueException
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset(self::SCALAR_TYPES[$type])) {
            if (is_scalar($data)) {
                switch ($type) {
                    case 'int':
                        return (int) $data;
                    case 'bool':
                        return (bool) $data;
                    case 'float':
                        return (float) $data;
                    case 'string':
                        return (string) $data;
                }
            }
        }

        if (! $this->normalizers) {
            throw new LogicException('You must register at least one normalizer to be able to denormalize objects.');
        }

        if ($normalizer = $this->getDenormalizer($data, $type, $format, $context)) {
            return $normalizer->denormalize($data, $type, $format, $context);
        }

        throw new NotNormalizableValueException(sprintf('Could not denormalize object of type "%s", no supporting normalizer found.', $type));
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $this->getNormalizer($data, $format, $context) !== null;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset(self::SCALAR_TYPES[$type]) || $this->getDenormalizer($data, $type, $format, $context) !== null;
    }

    final public function encode($data, string $format, array $context = [])
    {
        return $this->encoder->encode($data, $format, $context);
    }

    final public function decode(string $data, string $format, array $context = [])
    {
        return $this->decoder->decode($data, $format, $context);
    }

    public function supportsEncoding(string $format, array $context = [])
    {
        return $this->encoder->supportsEncoding($format, $context);
    }

    public function supportsDecoding(string $format, array $context = [])
    {
        return $this->decoder->supportsDecoding($format, $context);
    }

    /**
     * Returns a matching normalizer.
     *
     * @param mixed $data Data to get the serializer for
     * @param string $format Format name, present to give the option to normalizers to act differently based on formats
     * @param array $context Options available to the normalizer
     */
    private function getNormalizer($data, ?string $format, array $context): ?NormalizerInterface
    {
        $type = \is_object($data) ? \get_class($data) : 'native-' . \gettype($data);

        if (! isset($this->normalizerCache[$format][$type])) {
            $this->normalizerCache[$format][$type] = [];

            foreach ($this->normalizers as $k => $normalizer) {
                if (! $normalizer instanceof NormalizerInterface) {
                    continue;
                }

                if (! $normalizer instanceof CacheableSupportsMethodInterface || ! $normalizer->hasCacheableSupportsMethod()) {
                    $this->normalizerCache[$format][$type][$k] = false;
                } elseif ($normalizer->supportsNormalization($data, $format)) {
                    $this->normalizerCache[$format][$type][$k] = true;
                    break;
                }
            }
        }

        foreach ($this->normalizerCache[$format][$type] as $k => $cached) {
            $normalizer = $this->normalizers[$k];
            if ($cached || $normalizer->supportsNormalization($data, $format, $context)) {
                return $normalizer;
            }
        }

        return null;
    }

    /**
     * Returns a matching denormalizer.
     *
     * @param mixed $data Data to restore
     * @param string $class The expected class to instantiate
     * @param string $format Format name, present to give the option to normalizers to act differently based on formats
     * @param array $context Options available to the denormalizer
     */
    private function getDenormalizer($data, string $class, ?string $format, array $context): ?DenormalizerInterface
    {
        if (! isset($this->denormalizerCache[$format][$class])) {
            $this->denormalizerCache[$format][$class] = [];

            foreach ($this->normalizers as $k => $normalizer) {
                if (! $normalizer instanceof DenormalizerInterface) {
                    continue;
                }

                if (! $normalizer instanceof CacheableSupportsMethodInterface || ! $normalizer->hasCacheableSupportsMethod()) {
                    $this->denormalizerCache[$format][$class][$k] = false;
                } elseif ($normalizer->supportsDenormalization(null, $class, $format)) {
                    $this->denormalizerCache[$format][$class][$k] = true;
                    break;
                }
            }
        }

        foreach ($this->denormalizerCache[$format][$class] as $k => $cached) {
            $normalizer = $this->normalizers[$k];
            if ($cached || $normalizer->supportsDenormalization($data, $class, $format, $context)) {
                return $normalizer;
            }
        }

        return null;
    }
}
