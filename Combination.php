<?php

class Combination// implements \Iterator
{
    private $list;
    private $valid = true;

    public function __construct($list = array())
    {
        $this->fragments = count($list);

        foreach ($list as $charset) {

            //var_dump(new InfiniteIterator());
            $it = new CombinationFragment($charset);
            //$it->rewind();
            $this->list[] = $it;

          //   = $combinationFragment;
        }

    }

    public function valid()
    {
        return $this->valid;
    }

    public function current()
    {
        $result = array();
        foreach ($this->list as $fragment) {
            $result[] = $fragment->current();
        }

        return $result;
    }

    public function next()
    {
        $pos = 0;
        $this->list[$pos]->next();

        while ($this->list[$pos]->isBeginning()) {
            $pos ++;
            if ($pos > $this->fragments - 1) {
                $this->valid = false;
                break;
            }
            $this->list[$pos]->next();
        }
    }
}