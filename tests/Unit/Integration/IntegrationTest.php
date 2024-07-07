<?php

namespace NurAzliYT\LandProtections\Tests\Integration;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    /** @var Server */
    protected $server;

    /** @var PluginBase */
    protected $plugin;

    /** @var Player */
    protected $player;

    protected function setUp(): void
    {
        $this->server = Server::getInstance();
        $this->plugin = $this->server->getPluginManager()->getPlugin("LandProtections"); // Replace with your plugin name
        $this->assertInstanceOf(PluginBase::class, $this->plugin);

        // Creating a mock player for testing
        $this->player = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testPluginEnabled()
    {
        $this->assertTrue($this->plugin->isEnabled());
    }

    public function testPluginCommands()
    {
        // Example: Calling a command within the server
        $this->assertTrue($this->plugin->onCommand($this->player, "claim", "area_name", [])); // Adjust with relevant command and parameters
    }

    public function testPluginFunctionality()
    {
        // Example: Calling plugin functions to test functionality
        $this->assertTrue($this->plugin->claimArea("area_name", $this->player)); // Adjust with relevant plugin functions
    }

    protected function tearDown(): void
    {
        // Cleaning up state after tests
        unset($this->server, $this->plugin, $this->player);
    }
}
