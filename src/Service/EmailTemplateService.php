<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EmailDto\EmailDto;
use App\Entity\EmailKind;
use App\Entity\User;
use Symfony\Component\Mime\Address;

final readonly class EmailTemplateService
{
    public function __construct(
        private \Twig\Environment $twigEnvironment,
        private string $serverMail,
    ) {
    }

    /**
     * Send a "you have not logged in for a long time" email to the user.
     *
     * @param User[] $bccUsers the email address of the user to send the email
     *
     * @throws \Exception if the email content cannot be rendered
     */
    public function createLoginReminderDto(array $bccUsers): EmailDto
    {
        $textContent = <<<TEXT
            資料庫練功房 | 情況報告
            ====================

            登入天數 [↓ 減少]
            ---------------

            ⚠️ 我注意到這週你沒有登入，記得持續學習和練習對進步非常重要！
            提醒你一下，如果這週做題數量未達 5 題，每少做一題將會扣 4 分，
            希望你能儘快投入學習，保持進度，這樣才能持續提升自己的 SQL 能力。
            加油！

            立刻登入 → https://dbplay.pan93.com

            如果對信件有任何問題，請回報信件問題：
            https://dbplay.pan93.com/feedback?url=mail://text
            TEXT;

        try {
            $htmlContent = $this->twigEnvironment->render('email/mjml/remember-to-login.mjml.twig');
        } catch (\Throwable $e) {
            throw new \Exception('Failed to render the email content.', previous: $e);
        }

        $userAddresses = array_map(
            fn (User $user) => new Address(
                address: $user->getEmail(),
                name: $user->getName() ?? '',
            ),
            $bccUsers
        );

        return (new EmailDto())
            ->setToAddress($this->serverMail)
            ->setBcc($userAddresses)
            ->setSubject('[資料庫練功房] 登入次數警告')
            ->setText($textContent)
            ->setHtml($htmlContent)
            ->setKind(EmailKind::Transactional);
    }
}
