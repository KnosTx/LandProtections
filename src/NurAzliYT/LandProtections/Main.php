<?php

namespace NurAzliYT\LandProtections;

use pocketmine\plugin\PluginBase;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use NurAzliYT\events\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\Position;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use NurAzliYT\LandProtections\commands\ClaimCommand;
use NurAzliYT\LandProtections\commands\AccessLandCommand;

class Main extends PluginBase implements Listener {
    private Config $config;
    private array $claimedChunks = [];
    private array $chunkAccess = [];
    private ?BedrockEconomyAPI $economyAPI = null;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "claimedChunks.yml", Config::YAML);
        $this->claimedChunks = $this->config->getAll();
        $this->chunkAccess = $this->config->get("chunkAccess", []);

        // BedrockEconomy integration
        $this->economyAPI = BedrockEconomy::getInstance()->getAPI();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("claim", new ClaimCommand($this));
        $this->getServer()->getCommandMap()->register("accessland", new AccessLandCommand($this));
    }

    public function onDisable(): void {
        $this->config->setAll($this->claimedChunks);
        $this->config->set("chunkAccess", $this->chunkAccess);
        $this->config->save();
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $position = $block->getPosition();

        if ($this->isChunkClaimed($position)) {
            if (!$this->isChunkOwner($position, $player->getName()) && !$this->hasAccessToChunk($position, $player->getName())) {
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
            if (!$this->isChunkOwner($position, $player->getName()) && !$this->hasAccessToChunk($position, $player->getName())) {
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
            if (!$this->isChunkOwner($position, $player->getName()) && !$this->hasAccessToChunk($position, $player->getName())) {
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

    public function chargePlayer(Player $player, int $amount): bool {
        if ($this->economyAPI === null) {
            $player->sendMessage("Economy system not available.");
            return false;
        }

        $this->economyAPI->getPlayerBalance($player->getName(), function (?int $balance) use ($player, $amount) {
            if ($balance === null) {
                $player->sendMessage("Failed to retrieve balance.");
                return false;
            }

            if ($balance >= $amount) {
                $this->economyAPI->subtractFromPlayerBalance($player->getName(), $amount, function (bool $success) use ($player, $amount) {
                    if ($success) {
                        $player->sendMessage("You have been charged $amount coins.");
                    } else {
                        $player->sendMessage("Failed to charge your balance.");
                    }
                });
                return true;
            } else {
                $player->sendMessage("You do not have enough coins to claim this chunk.");
                return false;
            }
        });

        return true; // This assumes the balance retrieval is always async and successful.
    }

    public function giveAccessToChunk(Position $position, string $playerName): void {
        $chunkHash = $this->getChunkHash($position);
        if (!isset($this->chunkAccess[$chunkHash])) {
            $this->chunkAccess[$chunkHash] = [];
        }
        if (!in_array($playerName, $this->chunkAccess[$chunkHash])) {
            $this->chunkAccess[$chunkHash][] = $playerName;
        }
    }

    public function hasAccessToChunk(Position $position, string $playerName): bool {
        $chunkHash = $this->getChunkHash($position);
        return isset($this->chunkAccess[$chunkHash]) && in_array($playerName, $this->chunkAccess[$chunkHash]);
    }
}
