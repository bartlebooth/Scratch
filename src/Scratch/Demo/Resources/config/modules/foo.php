<?php

class Bam
{
    public $attr = 'default';
}

class Boum
{
    public $bam;

    public function __construct(Bam $bam)
    {
        $this->bam = $bam;
    }
}

return function ($get) {
    return [
        'test' => 'Boum',
        'bam' => function () {
            return new Bam();
        },
        'sharedBam' => function () {
            static $bam;

            if ($bam === null) {
                $bam = new Bam();
            }

            return $bam;
        },
        'boum' => function () use ($get) {
            return new Boum($get['moduleFoo::sharedBam']());
        }
    ];
};