<?php

namespace Phalcon\Image\Adapter;

class GDExt extends \Phalcon\Image\Adapter\GD {
    
    public function save($file = NULL, $quality = 75) {
        if (!$file) {
            $file = $this->getRealPath();
        }
        $result = False;
        switch ($this->getType()) {
            case 1://gif
                $result = imagegif($this->getImage(), $file);
                break;
            case 2://jpeg
                $result = imagejpeg($this->getImage(), $file, $quality);
                break;
            case 3://png
                $result = imagepng($this->getImage(), $file, $quality);
                break;
        }
        return $result;
    }
    public function autoRotate() {
        if ($this->getType() == 2) {
            $exif = exif_read_data($this->getRealPath());
            $rary = array('1' => 0, '8' => 270, '3' => 180, '6' => 90);
            if ($rary[$exif['Orientation']]) {
                return $this->rotate($rary[$exif['Orientation']]);
            }
        }
        return $this;
    }
}
