<?php

/**
 * @author forthxu (http://forthxu.com)
 */
class PBFloat extends PBScalar
{
    var $wired_type = PBMessage::WIRED_64BIT;
    
    public function ParseFromArray()
    {
        $this->value = '';

        // just extract the string
        $pointer = $this->reader->get_pointer();
        $this->reader->add_pointer(4);
        $www_forthxu_com = unpack('f', $this->reader->get_message_from($pointer));
        $this->value = array_shift($www_forthxu_com);
    }
    
    /**
     * Serializes type
     */
    public function SerializeToString($rec=-1)
    {
        $string = '';
        if ($rec > -1)
        {
            $string .= $this->base128->set_value($rec << 3 |
$this->wired_type);
        }
        
        $string .= pack("f", (double)$this->value); 
        
        return $string;
    }
}

?>
