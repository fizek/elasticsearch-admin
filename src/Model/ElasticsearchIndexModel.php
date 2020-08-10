<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIndexModel extends AbstractAppModel
{
    private $id;

    private $name;

    private $status;

    private $health;

    private $frozen;

    private $shards;

    private $replicas;

    private $documents;

    private $documentsDeleted;

    private $primarySize;

    private $totalSize;

    private $creationDate;

    private $settings;

    private $mappings;

    private $aliases;

    private $mappingsFlat;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHealth(): ?string
    {
        return $this->health;
    }

    public function setHealth(?string $health): self
    {
        $this->health = $health;

        return $this;
    }

    public function getFrozen(): ?string
    {
        return $this->frozen;
    }

    public function setFrozen(?string $frozen): self
    {
        $this->frozen = $frozen;

        return $this;
    }

    public function getShards(): ?int
    {
        return $this->shards;
    }

    public function setShards(?int $shards): self
    {
        $this->shards = $shards;

        return $this;
    }

    public function getReplicas(): ?int
    {
        return $this->replicas;
    }

    public function setReplicas(?int $replicas): self
    {
        $this->replicas = $replicas;

        return $this;
    }

    public function getDocuments(): ?int
    {
        return $this->documents;
    }

    public function setDocuments(?int $documents): self
    {
        $this->documents = $documents;

        return $this;
    }

    public function getDocumentsDeleted(): ?int
    {
        return $this->documentsDeleted;
    }

    public function setDocumentsDeleted(?int $documentsDeleted): self
    {
        $this->documentsDeleted = $documentsDeleted;

        return $this;
    }

    public function getPrimarySize(): ?int
    {
        return $this->primarySize;
    }

    public function setPrimarySize(?int $primarySize): self
    {
        $this->primarySize = $primarySize;

        return $this;
    }

    public function getTotalSize(): ?int
    {
        return $this->totalSize;
    }

    public function setTotalSize(?int $totalSize): self
    {
        $this->totalSize = $totalSize;

        return $this;
    }

    public function getCreationDate(): ?string
    {
        return $this->creationDate;
    }

    public function setCreationDate(?string $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings($settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSetting($key): ?string
    {
        return $this->settings[$key] ?? false;
    }

    public function setSetting(?string $key, ?string $value): self
    {
        $this->settings[$key] = $value;

        return $this;
    }

    public function getMappings(): ?array
    {
        return $this->mappings;
    }

    public function setMappings($mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getMappingsFlat(): ?array
    {
        return $this->mappingsFlat;
    }

    public function setMappingsFlat($mappingsFlat): self
    {
        $this->mappingsFlat = $mappingsFlat;

        return $this;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function setAliases($aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function hasMappingType(string $type): ?bool
    {
        if ($this->getMappingsFlat()) {
            foreach ($this->getMappingsFlat() as $mapping) {
                if (true === isset($mapping['type']) && $type == $mapping['type']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getExcludeSettings(): ?array
    {
        return [
            'index.creation_date',
            'index.provided_name',
            'index.uuid',
            'index.version.created',
            'index.version.upgraded',
            'index.number_of_shards',
            'index.shard.check_on_startup',
            'index.codec',
            'index.routing_partition_size',
            'index.load_fixed_bitset_filters_eagerly',
            'index.hidden',
        ];
    }

    public function convert(?array $index): self
    {
        if (true === isset($index['uuid'])) {
            $this->setId($index['uuid']);
        }

        $this->setName($index['index']);

        if (true === isset($index['status'])) {
            $this->setStatus($index['status']);
        }

        if (true === isset($index['health'])) {
            $this->setHealth($index['health']);
        }

        if (true === isset($index['sth'])) {
            $this->setFrozen($index['sth']);
        }

        if (true === isset($index['pri'])) {
            $this->setShards($index['pri']);
        }

        if (true === isset($index['rep'])) {
            $this->setReplicas($index['rep']);
        }

        if (true === isset($index['docs.count'])) {
            $this->setDocuments($index['docs.count']);
        }

        if (true === isset($index['docs.deleted'])) {
            $this->setDocumentsDeleted($index['docs.deleted']);
        }

        if (true === isset($index['pri.store.size'])) {
            $this->setPrimarySize($index['pri.store.size']);
        }

        if (true === isset($index['store.size'])) {
            $this->setTotalSize($index['store.size']);
        }

        if (true === isset($index['creation.date.string'])) {
            $this->setCreationDate($index['creation.date.string']);
        }

        if (true === isset($index['settings']) && 0 < count($index['settings'])) {
            $this->setSettings($index['settings']);
        }

        if (true === isset($index['mappings']) && 0 < count($index['mappings'])) {
            $this->setMappings($index['mappings']);
        }

        if (true === isset($index['mappings_flat']) && 0 < count($index['mappings_flat'])) {
            $this->setMappingsFlat($index['mappings_flat']);
        }

        if (true === isset($index['aliases']) && 0 < count($index['aliases'])) {
            $this->setAliases($index['aliases']);
        }

        return $this;
    }
}
