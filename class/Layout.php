<?php

namespace ComboStrap;

require_once(__DIR__ . "/LayoutArea.php");

class Layout
{
    const PAGE_SIDE = "page-side";
    const MAIN_FOOTER = "main-footer";
    const MAIN_SIDE = "main-side";
    const PAGE_MAIN = "page-main";

    /**
     * @var LayoutArea[]
     */
    private $layoutAreas;
    private $pageSideName;
    private $showPageSideSlot = null;
    /**
     * @var string
     */
    private $pageSideHtml;
    /**
     * @var string
     */
    private $mainFooterSlotName;
    private $showMainFooterSlot = null;
    private $mainFooterHtml;

    public static function create(): Layout
    {
        $layout = new Layout();
        $layout->getOrCreateArea(self::PAGE_SIDE)
            ->setSlotName(TplUtility::getSideSlotPageName());
        $layout->getOrCreateArea(self::MAIN_FOOTER)
            ->setSlotName(TplUtility::SLOT_MAIN_FOOTER);
        $layout->getOrCreateArea(self::MAIN_SIDE)
            ->setSlotName(TplUtility::getMainSideSlotName());
        return $layout;
    }

    public function getOrCreateArea($areaName): LayoutArea
    {
        $layoutArea = $this->layoutAreas[$areaName];
        if ($layoutArea === null) {
            $layoutArea = new LayoutArea($areaName);
            $this->layoutAreas[$areaName] = $layoutArea;
        }
        return $layoutArea;
    }

    public function setPageSideSlotName($sideBarName): Layout
    {
        $this->pageSideName = $sideBarName;
        return $this;
    }

    public function showPageSideSlot(): ?bool
    {
        return $this->showPageSideSlot;
    }

    public function getPageSideSlotName()
    {
        return $this->pageSideName;
    }

    public function setShowPageSideSlot(bool $showSideSlot): Layout
    {
        $this->showPageSideSlot = $showSideSlot;
        return $this;
    }

    public function setPageSideHtml(string $html): Layout
    {
        $this->pageSideHtml = $html;
        return $this;

    }

    public function setMainFooterSlotName(string $mainFooterSlotName): Layout
    {
        $this->mainFooterSlotName = $mainFooterSlotName;
        return $this;
    }

    public function showMainFooterSlot()
    {
        return $this->showMainFooterSlot;
    }

    public function getMainFooterHtml(): string
    {
        return $this->mainFooterHtml;
    }

    public function getMainFooterSlotName(): string
    {
        return $this->mainFooterSlotName;
    }

    public function setShowMainFooterSlot(bool $showMainFooterSlot)
    {
        $this->showMainFooterSlot = $showMainFooterSlot;
    }

    public function setMainFooterHtml(string $html): Layout
    {
        $this->mainFooterHtml = $html;
        return $this;
    }

}
