<?php

namespace NurAzliYT\LandProtections\events;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\Block;
use pocketmine\world\Position;

class BlockPlaceEvent extends Event implements Cancellable {
    private Player $player;
    private Block $block;
    private Position $position;
    private bool $isCancelled = false;

    public function __construct(Player $player, Block $block, Position $position) {
        $this->player = $player;
        $this->block = $block;
        $this->position = $position;
    }

    public function getPlayer(): Player {
        return $this->player;
    }

    public function getBlock(): Block {
        return $this->block;
    }

    public function getPosition(): Position {
        return $this->position;
    }

    public function cancel(): void {
        $this->setCancelled();
    }

    public function isCancelled(): bool {
        return $this->isCancelled;
    }

    public function setCancelled(bool $isCancelled = true): void {
        $this->isCancelled = $isCancelled;
    }
}
