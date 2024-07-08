<?php

namespace NurAzliYT\LandProtections\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginIdentifiableCommand;
use pocketmine\plugin\PluginOwnedTrait;
use NurAzliYT\LandProtections\Main;

class AccessLandCommand extends Command implements PluginIdentifiableCommand {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("accessland", "Give access to a chunk of land");
        $this->plugin = $plugin;
        $this->setPermission("landprotections.accessland");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage("Usage: /accessland <player_name>");
            return false;
        }

        $playerName = array_shift($args);
        $position = $sender->getPosition();

        if ($this->plugin->isChunkClaimed($position)) {
            if ($this->plugin->isChunkOwner($position, $sender->getName())) {
                $this->plugin->giveAccessToChunk($position, $playerName);
                $sender->sendMessage("Access to chunk granted to $playerName.");
            } else {
                $sender->sendMessage("You do not own this chunk.");
            }
        } else {
            $sender->sendMessage("This chunk is not claimed.");
        }

        return true;
    }

    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
