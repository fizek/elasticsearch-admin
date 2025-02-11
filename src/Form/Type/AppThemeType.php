<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\AppThemeManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppThemeType extends AbstractType
{
    protected AppThemeManager $appThemeManager;

    protected TranslatorInterface $translator;

    public function __construct(AppThemeManager $appThemeManager, TranslatorInterface $translator)
    {
        $this->appThemeManager = $appThemeManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $keys = $this->appThemeManager->keys();

        $i = 1;
        foreach ($keys as $key) {
            $builder->add($key, TextType::class, [
                'label' => 'theme_'.$key,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'data-break-after' => 6 == $i || 12 == $i || 18 == $i ? 'yes' : 'no',
                ],
            ]);
            $i++;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            //'data_class' => AppUserModel::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
