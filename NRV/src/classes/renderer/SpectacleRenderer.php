<?php

namespace nrv\renderer;

use nrv\festivale\Spectacle;

class SpectacleRenderer {

    private Spectacle $spectacle;

    public function __construct(Spectacle $spectacle) {
        $this->spectacle = $spectacle;
    }

    public function renderAsCompact(string $date, string $image) : string {
        return <<<FIN
            <li>
                <div>
                    <a href='?action=display-spectacle&id={$this->spectacle->__get('id')}'>{$this->spectacle->__get('titre')}</a>
                </div>
                <span>Date: $date, </span>
                <span>Horaire: {$this->spectacle->__get('horaire')}</span>
                <img src="/SAE_DevWeb/images/$image" alt="image spectacle">
            </li>
        FIN;
    }

    public function renderAsLong() {

    }

}