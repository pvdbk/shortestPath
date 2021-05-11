<?php

namespace Dependencies;

trait Injection {
    private static ?Provider $depProvider = null;

    private static function getDep($depName): string
    {
        if(self::$depProvider === null) {
            self::$depProvider = new Provider(get_called_class());
        }
        return self::$depProvider->get($depName);
    }

    private function getDepInstance($depName): object
    {
        return self::getDep($depName)::getInstance();
    }

    public function getConfig(): array
    {
        return Handler::getInstance()->getConfig();
    }
}
