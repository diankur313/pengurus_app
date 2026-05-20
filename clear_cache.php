<?php
echo "Clearing cache...\n";
system('php artisan optimize:clear');
echo "Done.\n";
