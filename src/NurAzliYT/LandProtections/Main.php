<?php

namespace NurAzliYT\LandProtections;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use NurAzliYT\LandProtections\commands\ClaimCommand;

class Main extends PluginBase implements Listener
{
    private BedrockEconomyAPI $economyAPI;
    private Config $claimedChunks;

    protected function onEnable(): void
    {
        $this->economyAPI = BedrockEconomy::getInstance()->getAPI();
        $this->claimedChunks = new Config($this->getDataFolder() . "claimedChunks.yml", Config::YAML);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("landprotections", new ClaimCommand($this));
    }

    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($this->isChunkClaimed($this->getChunkHash($block->getPosition())) &&
            !$this->isChunkOwner($player, $block->getPosition())) {
            $event->cancel();
            $player->sendMessage("This chunk is already claimed.");
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($this->isChunkClaimed($this->getChunkHash($block->getPosition())) &&
            !$this->isChunkOwner($player, $block->getPosition())) {
            $event->cancel();
            $player->sendMessage("You cannot break blocks in this claimed chunk.");
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($this->isChunkClaimed($this->getChunkHash($block->getPosition())) &&
            !$this->isChunkOwner($player, $block->getPosition())) {
            $event->cancel();
            $player->sendMessage("You cannot interact with blocks in this claimed chunk.");
        }
    }

    public function claimChunk(Player $player, Position $position): void
    {
        $chunkHash = $this->getChunkHash($position);
        $this->claimedChunks->set($chunkHash, $player->getName());
        $this->claimedChunks->save();
    }

    public function isChunkClaimed(string $chunkHash): bool
    {
        return $this->claimedChunks->exists($chunkHash);
    }

    public function isChunkOwner(Player $player, Position $position): bool
    {
        $chunkHash = $this->getChunkHash($position);
        return $this->claimedChunks->get($chunkHash) === $player->getName();
    }

    public function getChunkHash(Position $position): string
    {
        return ($position->getFloorX() >> 4) . ":" . ($position->getFloorZ() >> 4);
    }

    public function getBedrockEconomy(): BedrockEconomyAPI
    {
        return $this->economyAPI;
    }
}
