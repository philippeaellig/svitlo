<?php
namespace App\Twig;

use App\Entity\Child;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('gender', [$this, 'formatGender']),
        ];
    }

    public function formatGender($gender): string
    {
        return Child::$GENDER[$gender];
    }
}