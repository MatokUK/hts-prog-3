<?php

class CombinationFragment extends \InfiniteIterator
{
    private $firstItem;
    private $count;

    public function __construct($charset)
    {
        if (is_scalar($charset)) {
            $charset = str_split($charset, 1);
        }

        if (!isset($charset[0])) {
            var_dump($charset);
            exit;
        }
        $this->firstItem = $charset[0];
        $this->count = count($charset);

        parent::__construct(new ArrayIterator($charset));
        $this->rewind();
    }

    public function isBeginning()
    {
        return $this->current() == $this->firstItem;
    }
}