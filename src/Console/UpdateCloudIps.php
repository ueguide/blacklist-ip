<?php namespace App\Console\Commands\Analytics;

use Illuminate\Console\Command;

use App\Models\CloudIp;
use GuzzleHttp\Client;

class UpdateCloudIps extends Command
{
    protected $signature = 'blacklist_ip:cloud_ips';
    protected $description = 'Update database of cloud ips (bots).';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /* AMAZON WEB SERVICES */
        $awsUrl = 'https://ip-ranges.amazonaws.com/ip-ranges.json';
        
        $client = new Client();
        $response = $client->get($awsUrl);
        
        $aws = json_decode($response->getBody());
        
        foreach ($aws->prefixes as $obj) {
          CloudIp::updateOrCreate([
            'cidr_ip' => $obj->ip_prefix
          ],
          [
            'source' => 'Amazon Web Services',
            'region' => $obj->region,
            'updated_at' => time()
          ]);
        }
      
      
        /* MICROSOFT AZURE */
        $azureUrl = 'https://www.microsoft.com/en-us/download/confirmation.aspx?id=41653';
        
        $client = new Client();
        $response = $client->get($azureUrl);
        
        /* get xml uri */
        preg_match('#https://\S+PublicIPs\S+\.xml#', (string)$response->getBody(), $matches);
        
        $response = $client->get($matches[0]);
        
        $azure = simplexml_load_string((string)$response->getBody());
        
        foreach ($azure->Region as $region) {
          $regionName = $region['Name'];
          foreach ($region->IpRange as $range) {
            
            CloudIp::updateOrCreate([
              'cidr_ip' => $range['Subnet']
            ],
            [
              'source' => 'Microsoft Azure',
              'region' => $regionName,
              'updated_at' => time()
            ]);
          }
        }
        
        /* delete rows that haven't been updated in at least 3 days */
        $oldRows = CloudIp::where('updated_at', '<=', time() - 3 * 24 * 60 * 60)->delete();
    }
}