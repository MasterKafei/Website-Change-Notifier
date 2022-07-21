<?php

namespace App\Command;

use App\Message\Sender\ContentChangeSender;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(name: 'app:start', description: 'Notify the user if the url content is different from the previous call')]
class StartCommand extends Command
{
    private string $url;

    private string $path;

    private ?string $elementsContent = null;

    private string $projectDirectory;

    private string $phoneNumber;

    private ContentChangeSender $sender;

    #[Required]
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    #[Required]
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    #[Required]
    public function setProjectDirectory(KernelInterface $kernel): self
    {
        $this->projectDirectory = $kernel->getProjectDir();

        return $this;
    }

    #[Required]
    public function setContentChangeSender(ContentChangeSender $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    #[Required]
    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->getElementsContent() !== $this->getPageContent()) {
            $output->writeln('<error>The content of the page has changed</error>');
            $this->sender->notifyContentChange($this->phoneNumber);
        } else {
            $output->writeln('<info>The content of the page is the same</info>');
        }

        return Command::SUCCESS;
    }

    public function getPageContent(): string
    {
        $client = new HttpBrowser(HttpClient::create());
        $crawler = $client->request('GET', $this->url);

        $content = "";
        $crawler = $crawler->filter($this->path);
        foreach ($crawler as $element) {
            $content .= $element->nodeValue;
        }

        return $content;
    }

    public function getElementsContent(): string
    {
        $path = $this->projectDirectory . '/content.html';
        if (null === $this->elementsContent) {
            $fileSystem = new Filesystem();
            if (!$fileSystem->exists($path)) {
                $fileSystem->touch($path);
            }

            if (empty($this->elementsContent = file_get_contents($path))) {
                $this->elementsContent = $this->getPageContent();
                file_put_contents($path, $this->elementsContent);
            }
        }

        return $this->elementsContent;
    }
}
