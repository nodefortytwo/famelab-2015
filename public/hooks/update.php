<?php
exec('cd ' . __DIR__ . ' && git submodule update --remote');
die('Updated');