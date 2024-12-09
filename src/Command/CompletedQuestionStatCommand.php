<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\SolutionEventStatus;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:stat:completed-question', description: 'List the completed questions (and percentage) of users in database.')]
class CompletedQuestionStatCommand extends Command
{
    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly QuestionRepository $questionRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = new Table($io);
        $table->setHeaderTitle('Solved questions');
        $table->setHeaders(['Email', 'Passed', 'Total', 'Percent']);

        $totalQuestions = $this->questionRepository->count();
        if (0 === $totalQuestions) {
            $io->error('No questions found.');

            return Command::FAILURE;
        }

        /**
         * @var list<array{email: string, solved_questions: int<0, max>}> $solvedQuestions
         */
        $solvedQuestions = $this->solutionEventRepository->createQueryBuilder('se')
            ->select('u.email', 'COUNT(DISTINCT q) as solved_questions')
            ->join('se.question', 'q')
            ->join('se.submitter', 'u')
            ->where('se.status = :status')
            ->groupBy('u.email')
            ->orderBy('solved_questions', 'DESC')
            ->setParameter('status', SolutionEventStatus::Passed)
            ->getQuery()
            ->getResult();

        foreach ($solvedQuestions as $row) {
            $solvedQuestions = $row['solved_questions'];
            $table->addRow([$row['email'], $solvedQuestions, $totalQuestions, round($solvedQuestions / $totalQuestions * 100, 2).'%']);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
