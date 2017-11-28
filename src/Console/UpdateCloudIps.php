<?php

namespace TheLHC\BlacklistIp\Console;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Carbon\Carbon;

class UpdateCloudIps extends Command
{
    protected $signature = 'blacklist_ip:cloud_ips';
    protected $description = 'Update database of cloud ips (bots).';
    protected $config;

    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }
    
    /**
     * Handle command execution for laravel 5.1
     */
    public function fire()
    {
        $this->handle();
    }

    public function handle()
    {
        $this->downloadAwsRanges();
        $this->downloadAzureRanges();

        /* delete rows that haven't been updated in at least 3 days */
        $threeDays = date('Y-m-d H:i:s', time() - 3 * 24 * 60 * 60);
        \DB::table($this->config['cloudips_table'])
            ->where('updated_at', '<=', $threeDays)
            ->delete();
    }
    
    /**
     * AMAZON WEB SERVICES 
     * @return void
     */
    private function downloadAwsRanges()
    {
        $awsUrl = 'https://ip-ranges.amazonaws.com/ip-ranges.json';
        $client = new Client();
        $response = $client->get($awsUrl);
        $aws = json_decode($response->getBody());
        
        foreach ($aws->prefixes as $obj) {
            $timestamp = new Carbon;
            $attrs = [
                'cidr_ip' => $obj->ip_prefix,
                'source' => 'Amazon Web Services',
                'region' => $obj->region,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
            $record = \DB::table($this->config['cloudips_table'])
                            ->where('cidr_ip', $obj->ip_prefix)
                            ->first();
            if ($record) {
                unset($attrs['created_at']);
                \DB::table($this->config['cloudips_table'])
                                ->where('cidr_ip', $obj->ip_prefix)
                                ->update($attrs);
            } else {
                \DB::table($this->config['cloudips_table'])->insert($attrs);
            }
        }
    }
    
    /**
     * MICROSOFT AZURE
     * @return void 
     */
    private function downloadAzureRanges()
    {
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
                $timestamp = new Carbon;
                $attrs = [
                    'cidr_ip' => $range['Subnet'],
                    'source' => 'Microsoft Azure',
                    'region' => $regionName,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                $record = \DB::table($this->config['cloudips_table'])
                                ->where('cidr_ip', $range['Subnet'])
                                ->first();
                if ($record) {
                    unset($attrs['created_at']);
                    \DB::table($this->config['cloudips_table'])
                                    ->where('cidr_ip', $range['Subnet'])
                                    ->update($attrs);
                } else {
                    \DB::table($this->config['cloudips_table'])->insert($attrs);
                }
            }
        }
    }
}