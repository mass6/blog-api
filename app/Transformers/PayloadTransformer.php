<?php

namespace App\Transformers;

interface PayloadTransformer
{

    /**
     * Transform the payload into a formatted array
     *
     * @return Array
     */
    public function transform();
}