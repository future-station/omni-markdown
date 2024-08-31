<?php

arch('arch test')
    ->expect(['dd', 'dump', 'ray', 'die', 'var_dump', 'sleep', 'dispatch', 'dispatch_sync'])
    ->not->toBeUsed();
