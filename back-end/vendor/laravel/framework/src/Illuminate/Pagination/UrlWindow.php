<?php
namespace Illuminate\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as PaginatorContract;
class UrlWindow
{
    protected $paginator;
    public function __construct(PaginatorContract $paginator)
    {
        $this->paginator = $paginator;
    }
    public static function make(PaginatorContract $paginator)
    {
        return (new static($paginator))->get();
    }
    public function get()
    {
        $onEachSide = $this->paginator->onEachSide;
        if ($this->paginator->lastPage() < ($onEachSide * 2) + 6) {
            return $this->getSmallSlider();
        }
        return $this->getUrlSlider($onEachSide);
    }
    protected function getSmallSlider()
    {
        return [
            'first'  => $this->paginator->getUrlRange(1, $this->lastPage()),
            'slider' => null,
            'last'   => null,
        ];
    }
    protected function getUrlSlider($onEachSide)
    {
        $window = $onEachSide * 2;
        if (! $this->hasPages()) {
            return ['first' => null, 'slider' => null, 'last' => null];
        }
        if ($this->currentPage() <= $window) {
            return $this->getSliderTooCloseToBeginning($window);
        }
        elseif ($this->currentPage() > ($this->lastPage() - $window)) {
            return $this->getSliderTooCloseToEnding($window);
        }
        return $this->getFullSlider($onEachSide);
    }
    protected function getSliderTooCloseToBeginning($window)
    {
        return [
            'first' => $this->paginator->getUrlRange(1, $window + 2),
            'slider' => null,
            'last' => $this->getFinish(),
        ];
    }
    protected function getSliderTooCloseToEnding($window)
    {
        $last = $this->paginator->getUrlRange(
            $this->lastPage() - ($window + 2),
            $this->lastPage()
        );
        return [
            'first' => $this->getStart(),
            'slider' => null,
            'last' => $last,
        ];
    }
    protected function getFullSlider($onEachSide)
    {
        return [
            'first'  => $this->getStart(),
            'slider' => $this->getAdjacentUrlRange($onEachSide),
            'last'   => $this->getFinish(),
        ];
    }
    public function getAdjacentUrlRange($onEachSide)
    {
        return $this->paginator->getUrlRange(
            $this->currentPage() - $onEachSide,
            $this->currentPage() + $onEachSide
        );
    }
    public function getStart()
    {
        return $this->paginator->getUrlRange(1, 2);
    }
    public function getFinish()
    {
        return $this->paginator->getUrlRange(
            $this->lastPage() - 1,
            $this->lastPage()
        );
    }
    public function hasPages()
    {
        return $this->paginator->lastPage() > 1;
    }
    protected function currentPage()
    {
        return $this->paginator->currentPage();
    }
    protected function lastPage()
    {
        return $this->paginator->lastPage();
    }
}
