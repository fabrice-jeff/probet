<?php

namespace App\Command;

use App\Entity\Game;
use App\Repository\CountryRepository;
use App\Repository\GameRepository;
use App\Repository\StatusRepository;
use App\Utils\Constants\AppValuesConstants;
use App\Utils\Constants\FixedValuesConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-period-game',
    description: 'Add a short description for your command',
)]
class CreatePeriodGameCommand extends Command
{
    private EntityManagerInterface $manager;
    private CountryRepository $countryRepository;
    private StatusRepository $statusRepository;
    private GameRepository $gameRepository;

    public function __construct(EntityManagerInterface $manager, CountryRepository $countryRepository, StatusRepository $statusRepository, GameRepository $gameRepository)
    {
        $this->manager = $manager;
        $this->countryRepository = $countryRepository;
        $this->statusRepository = $statusRepository;
        parent::__construct();
        $this->gameRepository = $gameRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }
    
        $benin = $this->countryRepository->findOneBy(['code' => AppValuesConstants::CODE_BENIN]);
        // $togo =  $this->countryRepository->findOneBy(['code' => AppValuesConstants::CODE_TOGO]);
        $now = new \DateTime('now');
        $now->modify('+1 hour'); 
        $date = $now->format('Y-m-d');
        $dateTime = $now->format('Y-m-d H:i'); 
        
        $containsElevenGame = false; // OOh à 10h50
        $containsFourteen = false; // 11h à 13h50
        $containsEighteen = false; // 14h à 17h50
        $containsTwentyOne = false; // 18h à 20h50
        $containsTwentyThree = false; // 21h à 23h

        $games = $this->gameRepository->findGamesOfDay();
        foreach ($games as $game) {
            if($game->getStatus() === $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_ELEVEN])){
                $containsElevenGame = true;
            }
            else if($game->getStatus() === $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_FOURTEEN])){
                $containsFourteen = true;
            }
            else if($game->getStatus() === $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_EIGHTEEN] )){
                $containsEighteen = true;
            }
            else if($game->getStatus() === $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_ONE])){
                $containsTwentyOne = true;
            }
            else if($game->getStatus() === $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_TREE])){
                $containsTwentyThree = true;
            }
        }

        /**
         * Pour le jeu du Bénin
         */
        $gameBenin = new Game();
        $gameBenin->setCountry($benin);
        if($dateTime >= "$date 00:00" && $dateTime <= "$date 10:50"  && !$containsElevenGame){
            $gameBenin->setStartAt(new \DateTime("$date 00:00:00"));
            $gameBenin->setEndAt(new \DateTime("$date 10:50:00"));
            $gameBenin->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_ELEVEN]));
        }
        else if($dateTime >= "$date 11:00" && $dateTime <= "$date 13:50"  && !$containsFourteen){
            $gameBenin->setStartAt(new \DateTime("$date 11:00:00"));
            $gameBenin->setEndAt(new \DateTime("$date 13:50:00"));
            $gameBenin->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_FOURTEEN]));
        }
        else if($dateTime >= "$date 14:00"  && $dateTime <= "$date 17:50" && !$containsEighteen){
            $gameBenin->setStartAt(new \DateTime("$date 14:00:00"));
            $gameBenin->setEndAt(new \DateTime("$date 17:50:00"));
            $gameBenin->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_EIGHTEEN]));
        }
        else if($dateTime  >= "$date 18:00" && $dateTime <= "$date 20:50" && !$containsTwentyOne){
            $gameBenin->setStartAt(new \DateTime("$date 18:00:00"));
            $gameBenin->setEndAt(new \DateTime("$date 20:50:00"));
            $gameBenin->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_ONE]));
        }
        else if($dateTime  >= "$date 21:00" && $dateTime <= "$date 22:50" && !$containsTwentyThree){
            $gameBenin->setStartAt(new \DateTime("$date 21:00:00"));
            $gameBenin->setEndAt(new \DateTime("$date 22:50:00"));
            $gameBenin->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_TREE]));
        }
       
        if($gameBenin->getStartAt() != null) {
            $this->manager->persist($gameBenin);
            $this->manager->flush();
        }

        // /**
        //  * Pour le jeu de Togo
        //  */
        // $gameTogo = new Game();
        // $gameTogo->setCountry($togo);
        // if($dateTime >= "$date 09:10" && !$containsThirteen){
        //     $gameTogo->setStartAt(new \DateTime("$date 09:10:00"));
        //     $gameTogo->setEndAt(new \DateTime("$date 13:00:00"));
        //     $gameTogo->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_THIRTEEN]));
        // }
        // else if($dateTime >= "$date 13:10" && !$containsEighteenTogoGame){
        //     $gameTogo->setStartAt(new \DateTime("$date 13:10:00"));
        //     $gameTogo->setEndAt(new \DateTime("$date 18:00:00"));
        //     $gameTogo->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_EIGHTEEN]));
        // }
        // else if($dateTime  >= "$date 18:10" && !$containsNineGame){
        //     $gameTogo->setStartAt(new \DateTime("$date 18:10:00"));
        //     $gameTogo->setEndAt((new \DateTime("$date 09:00:00"))->modify('+1 day'));
        //     $gameTogo->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_NINE]));
        // }
        // if($gameTogo->getStartAt() != null) {
        //     $this->manager->persist($gameTogo);
        //     $this->manager->flush();
        // }
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
