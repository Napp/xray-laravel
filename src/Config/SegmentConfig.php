<?php

declare(strict_types=1);

namespace Napp\Xray\Config;

use Pkerrigan\Xray\Segment;

class SegmentConfig
{
    const NAME = 'name';

    /**
     * Segment annotations.
     *
     * Should be key-value string array.
     * ```
     * <?php
     * [
     *   'userId'   => $user->id,
     *   'userName' => $user->name,
     * ]
     * ?>
     * ```
     */
    const ANNOTATIONS = 'annotations';

    /**
     * Segment metadata.
     *
     * Should be key-value string array.
     * ```
     * <?php
     * [
     *   'backtrace' => $error->backtrace,
     * ]
     * ?>
     * ```
     */
    const METADATA = 'metadata';

    /**
     * Specify subsegment parent.
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
     */
    const PARENT_SEGMENT = 'fixedParent';

    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config = []) {
        $this->config = $config;
    }

    public function applyTo(Segment $segment)
    {
        if (isset($this->config[SegmentConfig::NAME])) {
            $segment->setName($this->config[SegmentConfig::NAME]);
        }
        if (isset($this->config[SegmentConfig::METADATA])) {
            foreach ($this->config[SegmentConfig::METADATA] as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $segment->addMetadata($key, $value);
                }
            }
        }
        if (isset($this->config[SegmentConfig::ANNOTATIONS])) {
            foreach ($this->config[SegmentConfig::ANNOTATIONS] as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $segment->addAnnotation($key, $value);
                }
            }
        }
    }

    public function getParentSegment(): ?Segment
    {
        return $this->config[SegmentConfig::PARENT_SEGMENT] ?? null;
    }
}
