<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIndexSettingModel extends AbstractAppModel
{
    private ?string $name = null;

    private ?string $value = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            $this->getName() => $this->getValue(),
        ];

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
