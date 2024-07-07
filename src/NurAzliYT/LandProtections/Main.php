<?php

namespace NurAzliYT\LandProtections;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use NurAzliYT\LandProtections\commands\ClaimCommand;

class Main extends PluginBase implements Listener {

    private Config $config;
    private array $claimedChunks = [];

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
        $position = $block->getPosition();

        if ($this->isChunkClaimed($position)) {
            if (!$this->isChunkOwner($position, $player->getName())) {
                $event->cancel();
                $player->sendMessage("This chunk is claimed by someone else.");
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $position = $block->getPosition();

        if ($this->isChunkClaimed($position)) {
            if (!$this->isChunkOwner($position, $player->getName())) {
                $event->cancel();
                $player->sendMessage("This chunk is claimed by someone else.");
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $position = $block->getPosition();

        if ($this->isChunkClaimed($position)) {
            if (!$this->isChunkOwner($position, $player->getName())) {
                $event->cancel();
                $player->sendMessage("This chunk is claimed by someone else.");
            }
        }
    }

    public function claimChunk(Position $position, string $owner): void {
        $chunkHash = $this->getChunkHash($position);
        $this->claimedChunks[$chunkHash] = $owner;
    }

    public function isChunkClaimed(Position $position): bool {
        $chunkHash = $this->getChunkHash($position);
        return isset($this->claimedChunks[$chunkHash]);
    }

    public function isChunkOwner(Position $position, string $owner): bool {
        $chunkHash = $this->getChunkHash($position);
        return isset($this->claimedChunks[$chunkHash]) && $this->claimedChunks[$chunkHash] === $owner;
    }

    public function getChunkHash(Position $position): string {
        return ($position->getFloorX() >> 4) . ":" . ($position->getFloorZ() >> 4);
    }
}
