<?php

namespace App\Form\Type;

use App\Model\ElasticsearchClusterSettingModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchClusterSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'value';
        $fields[] = 'is_array';

        foreach ($fields as $field) {
            switch ($field) {
                case 'value':
                    $builder->add('value', TextType::class, [
                        'label' => 'value',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'is_array':
                    $builder->add('is_array', CheckboxType::class, [
                        'label' => 'is_array',
                        'required' => false,
                        'help' => 'help_form.cluster_setting.is_array',
                        'help_html' => true,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchClusterSettingModel::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
