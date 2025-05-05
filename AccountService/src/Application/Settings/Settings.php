<?php

declare(strict_types=1);

namespace App\Application\Settings;

class Settings implements SettingsInterface
{
    private array $settings;

    public function __construct(array $settings)
    {
        // Define valores padrão para evitar erros de chave ausente
        $defaults = [
            'displayErrorDetails' => true,
            'logError' => true,
            'logErrorDetails' => true,
        ];
        $this->settings = array_merge($defaults, $settings);
    }

    /**
     * @return mixed
     */
    public function get(string $key = '')
    {
        return (empty($key)) ? $this->settings : $this->settings[$key];
    }
}
