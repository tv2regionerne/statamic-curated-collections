<?php

namespace Tv2regionerne\StatamicCuratedCollection\Data\Traits;

trait Fillable
{
    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            } elseif (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }
}
