<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\Indexer\TNTGeoIndexer;
use App\Order;
use Exception;
class IndexOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index the order table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $tnt = new TNTSearch;
       $tnt->loadConfig([
          'driver'    => 'mysql',
          'host'      => 'localhost',
          'database'  => 'aci',
          'username'  => 'root',
          'password'  => 'root',
          'storage'   => '/var/www/html/aci/storage/custom/'
        ]);
        $indexer = $tnt->createIndex('places.index');
        $indexer->query('SELECT id, order_number from orders;');
        $indexer->run();


    }
}
