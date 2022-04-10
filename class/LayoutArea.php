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

    public function __construct(string $areaId)
    {
        $this->areaId = $areaId;
    }


    /**
     * @var bool show or not the area
     */
    private $show;
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

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
