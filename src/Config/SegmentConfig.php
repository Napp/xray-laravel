<?php

declare(strict_types=1);

namespace Napp\Xray\Config;

use Pkerrigan\Xray\Segment;

class SegmentConfig
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * Key-value string array for annotations
     *
     * @var string[]|null
     */
    protected $annotations;

    /**
     *
     * @var string[]|null
     */
    protected $metadata;

    /**
     * @var Segment|null
     */
    protected $parent;

    public function __construct(?string $name = null) {
        $this->name = $name;
    }

    /**
     * Key-value string array for annotations.
     *
     * ```
     * [
     *   'userId'   => $user->id,
     *   'userName' => $user->name,
     * ]
     * ```
     *
     * @param string[] $annotations
     * @return static
     */
    public function setAnnotations(array $annotations) {
        $this->annotations = $annotations;
        return $this;
    }

    /**
     * Key-value string array for metadata.
     *
     * ```
     * [
     *   'backtrace' => $error->backtrace,
     * ]
     * ```
     *
     * @param string[] $metadata
     * @return static
     */
    public function setMetadata(array $metadata) {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Specify parent segment to avoid nested structure
     *
     * If not provided, it will use first unclosed segment as parent. It is
     * useful when you need to add multiple segments as async process.
     *
     * For example:
     *
     * ```
     * |--- A -------
     * | |--- B -----
     * | | |--- C ---
     * |
     * | |--- D -----
     * ```
     *
     * Add new segment `E` will select segment `C` as parent and fired
     * `addSubsegment` on it.
     * Add new segment `F` continuously will have result:
     *
     * ```
     * |--- A ---------
     * | |--- B -------
     * | | |--- C -----
     * | | | |--- E ---
     * | | | | |--- F -
     * |
     * | |--- D -------
     * ```
     *
     * But specify parent segment as `B` can have result:
     *
     * ```
     * |--- A -------
     * | |--- B -----
     * | | |--- C ---
     * | | |--- E ---
     * | | |--- F ---
     * |
     * | |--- D -----
     * ```
     *
     * @param Segment $segment
     * @return static
     */
    public function setParent(Segment $segment) {
        $this->parent = $segment;
        return $this;
    }

    public function getParent(): ?Segment
    {
        return $this->parent;
    }

    public function applyTo(Segment $segment)
    {
        if (isset($this->name)) {
            $segment->setName($this->name);
        }
        if (isset($this->metadata)) {
            foreach ($this->metadata as $key => $value) {
                $segment->addMetadata($key, $value);
            }
        }
        if (isset($this->annotations)) {
            foreach ($this->annotations as $key => $value) {
                $segment->addAnnotation($key, $value);
            }
        }
    }
}
