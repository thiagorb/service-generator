<?php

namespace Thiagorb\ServiceGenerator\Targets\Models;

class PropertyTypeResolver
{
    public function isNullable(array $propertyData): bool
    {
        return $this->parseTypeName($propertyData['type'])['nullable'];
    }

    public function resolveTypeHint(array $propertyData): string
    {
        return $this->parseTypeName($propertyData['type'])['type_hint'];
    }

    protected function parseTypeName(string $typeName)
    {
        $classPattern = '(\\\\[a-z][a-z0-9_]*)+';
        $simpleTypePattern = "string|int|float|bool|array|$classPattern";

        $typePattern = "/^(?P<nullable_simple>\?)?(?P<simple>$simpleTypePattern)((?P<array>\[\])(?P<nullable_array>\|null)?)?$/i";

        if (!preg_match($typePattern, $typeName, $matches)) {
            throw new \Error('Unable to resolve type ' . $typeName);
        }

        $isArray = ($matches['array'] ?? '') === '[]';
        $typeHint = $isArray ? 'array' : $matches['simple'];
        $nullable = $isArray ? ($matches['nullable_array'] ?? '') === '|null' : ($matches['nullable_simple'] ?? '') === '?';

        return ['nullable' => $nullable, 'type_hint' => $typeHint];
    }
}
