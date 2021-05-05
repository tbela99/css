<?php 

namespace TBela\CSS\Interfaces;

/**
 * Interface Renderable
 * @package TBela\CSS
 * @method getName(): string;
 * @method getType(): string;
 * @method getValue(): \TBela\CSS\Value\Set;
 */
interface RenderableInterface extends ParsableInterface {

    /**
     * @param array|null $comments
     * @return RenderableInterface
     */
    public function setTrailingComments(?array $comments): RenderableInterface;

    /**
     * @return string[]|null
     */
    public function getTrailingComments(): ?array;

    /**
     * @param string[]|null $comments
     * @return RenderableInterface
     */
    public function setLeadingComments(?array $comments): RenderableInterface;

    /**
     * @return string[]|null
     */
    public function getLeadingComments(): ?array;
}