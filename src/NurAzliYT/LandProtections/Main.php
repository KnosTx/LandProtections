<?php

namespace NurAzliYT\LandProtections;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\level\Position;

class Main extends PluginBase implements Listener {

    private $claimedChunks = [];
    private $config;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "claimedChunks.yml", Config::YAML);
        $this->claimedChunks = $this->config->getAll();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("claim", new ClaimCommand($this));
    }

    public function onDisable(): void {
        $this->config->setAll($this->claimedChunks);
        $this->config->save();
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $position = $block->asPosition();

        if ($this->isChunkClaimed($position) && !$this->isChunkOwner($position, $player->getName())) {
            $player->sendMessage("This chunk is already claimed by another player.");
            $event->setCancelled();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $position = $block->asPosition();

        if ($this->isChunkClaimed($position) && !$this->isChunkOwner($position, $player->getName())) {
            $player->sendMessage("You cannot place blocks in a chunk that you do not own.");
            $event->setCancelled();
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $position = $block->asPosition();

        if ($this->isChunkClaimed($position) && !$this->isChunkOwner($position, $player->getName())) {
            $player->sendMessage("You cannot break blocks in a chunk that you do not own.");
            $event->setCancelled();
        }
    }

    public function claimChunk(Position $position, string $playerName): void {
        $chunkHash = $this->getChunkHash($position);
        $this->claimedChunks[$chunkHash] = $playerName;
    }

    private function isChunkClaimed(Position $position): bool {
        $chunkHash = $this->getChunkHash($position);
        return isset($this->claimedChunks[$chunkHash]);
    }

    private function isChunkOwner(Position $position, string $playerName): bool {
        $chunkHash = $this->getChunkHash($position);
        return $this->claimedChunks[$chunkHash] === $playerName;
    }

    private function getChunkHash(Position $position): string {
        $chunkX = $position->getFloorX() >> 4;
        $chunkZ = $position->getFloorZ() >> 4;
        return $chunkX . ":" . $chunkZ;
    }
}
