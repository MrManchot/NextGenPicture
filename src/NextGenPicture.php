<?php

class NextGenPicture
{
    private static $config = [
        'debug' => false,
        'relative_path' => '../cache/',
        'cache_dir' => __DIR__ . '/../cache/',
        'force_generate' => false,
        'quality' => 85,
        'max_pixel_ratio' => 3
    ];
    public $breakpoints = [];
    public $alt;
    public $class;
    public $id;
    public $attributes;

    public function __construct($configs = [])
    {
        if (!empty($configs)) {
            foreach ($configs as $key => $value) {
                if (array_key_exists($key, self::$config)) {
                    $methodName = 'set' . ucfirst($key);
                    if (method_exists(static::class, $methodName)) {
                        self::$config[$key] = self::{$methodName}($value);
                    } else {
                        self::$config[$key] = $value;
                    }
                }
            }
        }
        $this->testCacheDir();
        $this->testConfiguation();
    }

    private function testConfiguation()
    {
        $configuraton = true;
        $commands = [
            'convert' => 'imagemagick',
            'cwebp' => 'webp'
        ];
        foreach ($commands as $command => $package) {
            exec($command . ' -h 2>&1', $output);
            if (stripos($output[0], 'not found') !== false) {
                throw new Exception($command . ' not install on your server : sudo apt install ' . $package . ' (on Ubuntu)');
                $configuraton = false;
            }
        }
        return $configuraton;
    }

    private function testCacheDir()
    {
        if (!is_dir(self::$config['cache_dir'])) {
            @mkdir(self::$config['cache_dir']);
            if (!is_dir(self::$config['cache_dir'])) {
                throw new Exception('Cache directory does not exist : ' . self::$config['cache_dir']);
            }
        }
        if (!is_writable(self::$config['cache_dir'])) {
            @chmod(self::$config['cache_dir'], 755);
            if (!is_writable(self::$config['cache_dir'])) {
                throw new Exception('Cache directory is not writable : ' . self::$config['cache_dir']);
            }
        }
    }

    private function getSize()
    {
        $size = getimagesize($this->file);
        $this->original_width = $size[0];
        $this->original_height = $size[1];
    }

    private function getExtension()
    {
        $exif = exif_imagetype($this->file);
        if ($exif == IMAGETYPE_PNG) {
            $this->extension = 'png';
        } elseif ($exif == IMAGETYPE_JPEG) {
            $this->extension = 'jpg';
        } else {
            throw new Exception('This file format is not allowed : ' . $this->file);
        }
    }


    public function setCompatibility($compatibility = true)
    {
        $this->compatibility = $compatibility;
        return $this;
    }

    private function tryLoadFile($file)
    {
        if (!file_exists($file)) {
            $file_content = @file_get_contents($file);
            if (strlen($file_content)) {
                $local_file = self::$config['cache_dir'] . uniqid() . '.tmp';
                file_put_contents($local_file, $file_content);
                if (file_exists($local_file)) {
                    $file = $local_file;
                } else {
                    $file = false;
                }
            } else {
                $file = false;
            }
        }
        return $file;
    }

    public function reinit()
    {
        $this->getExtension();
        $this->getSize();
        $this->setCompatibility();
        $this->setAlt()->setId()->setClass()->setAttributes()->setMaxDisplaySize()->setResponsive();
        $this->sizes = [];
    }

    public function load($file)
    {
        $file = $this->tryLoadFile($file);
        if ($file) {
            $this->file = $file;
            $this->basename = md5_file($this->file);
            $this->reinit();
        } else {
            throw new Exception('File does not exit : ' . $file);
        }
        return $this;
    }

    private function setQuality($quality)
    {
        if (is_numeric($quality) && $quality >= 0 && $quality <= 100) {
            return intval($quality);
        } else {
            throw new Exception('The quality value must be between 0 and 100');
            return self::$config['quality'];
        }
    }

    public function setAlt($alt = '')
    {
        $this->alt = htmlspecialchars($alt);
        return $this;
    }

    public function setId($id = '')
    {
        $this->id = $id;
        return $this;
    }

    public function setMaxDisplaySize($sizes = 0)
    {
        if (is_numeric($sizes)) {
            $this->max_display_width = $sizes;
        } elseif (is_array($sizes)) {
            $this->max_display_width = $this->getWidth($sizes[0], $sizes[1]);
        }
        return $this;
    }

    public function setResponsive($breakpoints = [])
    {
        $this->breakpoints = $breakpoints;
        asort($this->breakpoints);
        return $this;
    }

    private function getWidth($width, $height)
    {
        $width_ratio = $width / $this->original_width;
        $height_ratio = $height / $this->original_height;
        if ($height_ratio < $width_ratio) {
            return $this->original_width  * $height_ratio;
        } else {
            return $width;
        }
    }

    private function getAllSizes()
    {
        for ($i = 1; $i <= self::$config['max_pixel_ratio']; $i++) {

            if (is_array($this->breakpoints) && !empty($this->breakpoints)) {
                foreach ($this->breakpoints as $breakpoint => $sizes) {
                    if (is_numeric($sizes)) {
                        $width = $sizes;
                    } elseif (is_array($sizes)) {
                        $width = $this->getWidth($sizes[0], $sizes[1]);
                    }
                    $test_size = intval(round($width * $i));
                    if ($test_size <= $this->original_width) {
                        $this->sizes[$breakpoint][$i] = $test_size;
                    }
                }
            }

            if (isset($this->max_display_width)) {
                $this->max_display_width = intval(round($this->max_display_width));
            }
            $default_size = isset($this->max_display_width) && $this->max_display_width ? $this->max_display_width : $this->original_width;
            $test_size = $default_size * $i;
            if ($test_size <= $this->original_width) {
                $this->sizes['original'][$i] = $test_size;
            }
        }
        if (!isset($this->sizes['original'][1])) {
            $this->sizes['original'][1] = $this->original_width;
        }
    }

    public function setClass($classes = '')
    {
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        $this->class = $classes;
        return $this;
    }

    public function setAttributes($attributes = [])
    {
        $html = '';
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $html .= $key . '="' . $value . '" ';
            }
        }
        $this->attributes = $html;
        return $this;
    }

    private function process()
    {
        $this->getAllSizes();
        foreach ($this->sizes as $breakpoint => $sizes) {
            foreach ($sizes as $width) {
                $export_base = self::$config['cache_dir'] . $this->basename . '_' . $width . '.';
                $export = $export_base . $this->extension;
                if (!file_exists($export) || self::$config['force_generate']) {
                    $this->addCmd('convert ' . $this->file . ' -resize ' . $width . ' ' . $export);
                    $this->addCmd('cwebp -q ' . self::$config['quality'] . ' ' . $export . ' -o ' . $export_base . 'webp');
                    if ($this->compatibility) {
                        if ($this->extension == 'jpg') {
                            $this->addCmd('jpegoptim ' . $export . ' --strip-all -o  --m ' . self::$config['quality']);
                        } elseif ($this->extension == 'png') {
                            $this->addCmd('pngquant ' . $export . ' -o ' . $export . ' --force');
                        }
                    }
                }
            }
        }
        if (!empty($this->cmd)) {
            foreach ($this->cmd as $line) {
                exec($line, $output);
                if ($output) {
                    $this->output[]  = implode("\n", $output);
                }
            }
        }
    }

    private function addCmd($cmd)
    {
        $this->cmd[] = $cmd . ' 2>&1';
    }

    public function display()
    {
        $this->process();
        $this->relative_path = self::$config['relative_path'] . $this->basename . '_';

        $html =  '<picture>' . PHP_EOL;
        foreach ($this->sizes as $breakpoint => $size) {
            $html .= $this->getSource($breakpoint, $size);
        }
        if ($this->compatibility) {
            foreach ($this->sizes as $breakpoint => $size) {
                $html .= $this->getSource($breakpoint, $size, $this->extension);
            }
        }
        $src = $this->relative_path . $this->sizes['original'][1] . '.' . ($this->compatibility ? $this->extension : 'webp');
        $html .= '  <img src="' . $src  . '" ' .
            ($this->class ? 'class="' . $this->class . '" ' : '') .
            ($this->id ? 'id="' . $this->id . '" ' : '') .
            'alt="' . $this->alt . '" ' .
            $this->attributes .
            '>' . PHP_EOL;

        $html .= '</picture>' . PHP_EOL;
        return $html;
    }

    private function getSource($breakpoint, $size, $extension = 'webp')
    {
        $media = '';
        $type = '';
        if ($breakpoint != 'original') {
            $media = 'media="(max-width: ' . $breakpoint . 'px)" ';
        }
        if ($extension == 'webp') {
            $type = 'type="image/webp" ';
        }
        $srcsets = [];
        foreach ($size as $ratio => $width) {
            $srcsets[] = $this->relative_path . $width . '.' . $extension . (count($size) > 1 ? ' ' . $ratio . 'x' : '');
        }

        return '  <source ' . $type . $media . 'srcset="' . implode(', ', $srcsets) . '">' . PHP_EOL;
    }
}
