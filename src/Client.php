<?php

declare(strict_types=1);

namespace Keven\RedBySfr;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Client
{
    private HttpClientInterface $httpClient;
    private string $identifier, $password;
    private ?string $contractNumber;
    private bool $isAuthenticated = false;

    public function __construct(string $identifier, string $password, string $contractNumber = null, HttpClientInterface $httpClient = null)
    {
        $this->identifier = $identifier;
        $this->password = $password;
        $this->contractNumber = $contractNumber;
        $this->httpClient = $httpClient ?: HttpClient::createForBaseUri('https://espace-client-red.sfr.fr');
    }

    private function authenticateIfRequired(): void
    {
        if (!$this->isAuthenticated) {
            $this->authenticate($this->identifier, $this->password);
            $this->isAuthenticated = true;
        }
    }

    private function authenticate(string $identifier, string $password): void
    {
        $response = $this->httpClient->request('GET', 'https://www.sfr.fr/cas/login?domain=espaceclientred&service=https%3A%2F%2Fwww.red-by-sfr.fr%2Faccueil%2Fj_spring_cas_security_check');
        $crawler = new Crawler($response->getContent());

        $data = array_column(
            $crawler
                ->filter('form[name=loginForm]')
                ->children('input')
                ->extract(['name', 'value']),
            1,
            0
        );
var_dump($response->getStatusCode());
$response->getContent();
print_r($response->getInfo());
        $data['username'] = $identifier;
        $data['password'] = $password;
        $data['remember-me'] = 'on';
        $data['identifier'] = null;
print_r($data);
        $response2 = $this->httpClient->request('POST', 'https://www.sfr.fr/cas/login?domain=espaceclientred&service=https%3A%2F%2Fwww.red-by-sfr.fr%2Faccueil%2Fj_spring_cas_security_check', [
            'body' => $data,
            'headers' => [
                'Referer' => 'https://www.sfr.fr/cas/login?service=https%3A%2F%2Fwww.sfr.fr%2Faccueil%2Fj_spring_cas_security_check',
            ]
        ]);

var_dump($response2->getStatusCode());
$response2->getContent();
print_r($response2->getInfo());
//$response3 = $this->httpClient->request('GET', 'https://www.sfr.fr/accueil/j_spring_cas_security_check?ticket='.urlencode());
        if (!preg_match('/Info conso/', $response2->getContent())) {
            throw new \RuntimeException('Authentication failed.');
        }

        if (null !== $this->contractNumber) {
            $this->httpClient->request('GET', 'https://www.red-by-sfr.fr/mon-espace-client/?e='.base64_encode($this->contractNumber));
        }
    }

    public function invoices(): \Generator
    {
        $this->authenticateIfRequired();

        $response = $this->httpClient->request('GET', 'https://espace-client-red.sfr.fr/facture-fixe/infoconso');
        preg_match_all('#/facture-fixe/telecharger/facture/\d+/\d+#', $response->getContent(), $matches);
        print_r($matches);
        die;
    }
}
