<?php

namespace Larva\Flysystem\Qiniu;

interface VisibilityConverter
{
    public function visibilityToAcl(string $visibility): string;

    public function aclToVisibility(string $acl): string;

    public function defaultForDirectories(): string;
}
