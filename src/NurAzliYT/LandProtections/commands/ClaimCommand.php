<?php

namespace NurAzliYT\LandProtections\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginIdentifiableCommand;
use pocketmine\plugin\PluginOwnedTrait;
use NurAzliYT\LandProtections\Main;

class ClaimCommand extends Command implements PluginIdentifiableCommand {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("claim", "Claim a chunk of land");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        $position = $sender->getPosition();
        if ($this->plugin->isChunkClaimed($position)) {
            $sender->sendMessage("This chunk is already claimed.");
        } else {
            $this->plugin->claimChunk($position, $sender->getName());
            $sender->sendMessage("Chunk claimed successfully!");
        }

        return true;
    }

    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
