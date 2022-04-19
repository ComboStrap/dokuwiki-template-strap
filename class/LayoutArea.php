<?php


namespace ComboStrap;

/**
 * Represents a layout area
 */
class LayoutArea
{
    /**
     * @var string
     */
    private $areaId;
    /**
     * @var string
     */
    private $html = "";
    private $slotName = "";

    public function __construct(string $areaId)
    {
        $this->areaId = $areaId;
    }


    /**
     * @var bool show or not the area
     * null means that combo is not installed
     * because there is no true/false
     * and the rendering is done at the dokuwiki way
     */
    private $show = null;
    private $attributes = [];

    public function setShow(bool $show)
    {
        $this->show = $show;
    }

    public function toEnterHtmlTag(string $string): string
    {
        $htmlAttributesAsArray = [];
        foreach ($this->attributes as $attribute => $value) {
            $attribute = htmlspecialchars($attribute, ENT_XHTML | ENT_QUOTES);
            $value = htmlspecialchars($value, ENT_XHTML | ENT_QUOTES);
            $htmlAttributesAsArray[] = "$attribute=\"$value\"";
        };
        $htmlAttributesAsString = implode(" ", $htmlAttributesAsArray);
        return "<$string id=\"$this->areaId\" $htmlAttributesAsString>";
    }

    public function setAttributes(array $attributes): LayoutArea
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setHtml(string $html): LayoutArea
    {
        $this->html = $html;
        return $this;
    }

    public function getSlotName()
    {
        return $this->slotName;
    }

    public function setSlotName($slotName): LayoutArea
    {
        $this->slotName = $slotName;
        return $this;
    }

    public function show(): ?bool
    {
        return $this->show;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

}
