<?php

namespace ComboStrap;

require_once(__DIR__ . "/LayoutArea.php");

class Layout
{
    const PAGE_SIDE = "page-side";
    const MAIN_FOOTER = "main-footer";
    const MAIN_SIDE = "main-side";
    const PAGE_MAIN = "page-main";
    const PAGE_HEADER = "page-header";
    const PAGE_FOOTER = "page-footer";

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
        $layout->getOrCreateArea(self::PAGE_HEADER)
            ->setSlotName(TplUtility::getHeaderSlotPageName());
        $layout->getOrCreateArea(self::PAGE_FOOTER)
            ->setSlotName(TplUtility::getFooterSlotPageName());
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


}
