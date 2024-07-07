<?php

namespace NurAzliYT\LandProtections;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class ClaimCommand extends Command implements PluginIdentifiableCommand, PluginOwned {
    use PluginOwnedTrait;

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("claim", "Claim a chunk of land", "/claim");
        $this->setPermission("landprotections.claim");
        $this->plugin = $plugin;
        $this->owningPlugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        if (!$sender->hasPermission("landprotections.claim")) {
            $sender->sendMessage("You do not have permission to use this command.");
            return false;
        }

        $position = $sender->getPosition();
        if ($this->plugin->isChunkClaimed($position)) {
            $sender->sendMessage("This chunk is already claimed.");
            return false;
        }

        $this->plugin->claimChunk($position, $sender->getName());
        $sender->sendMessage("Chunk successfully claimed!");
        return true;
    }

    public function getOwningPlugin(): \pocketmine\plugin\Plugin {
        return $this->plugin;
    }
}
