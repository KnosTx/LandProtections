<?php

namespace NurAzliYT\LandProtections\commands;

use pocketmine\plugin\Plugin;

interface PluginIdentifiableCommand {
    /**
     * Gets the owning plugin of this command.
     *
     * @return Plugin The plugin that owns this command.
     */
    public function getPlugin(): Plugin;
}
