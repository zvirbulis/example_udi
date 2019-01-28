<?php


namespace App\Udi\Customization\Models;

/**
 * App\Udi\Customization\Models\StudentProfile
 *
 * @property int $user_id
 * @property string|null $medical_information
 * @property mixed|null $medical_files_id
 * @property-read mixed $medical_files
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\StudentProfile whereMedicalFilesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\StudentProfile whereMedicalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\StudentProfile whereUserId($value)
 * @mixin \Eloquent
 * @property int $at_school
 * @property float $canteen_balance
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\StudentProfile whereAtSchool($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\StudentProfile whereCanteenBalance($value)
 */
class StudentProfile extends \App\Models\StudentProfile
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->casts['StudentProfile.medical_files_id'] = 'medical_files_id';
    }

    protected function castAttribute($key, $value)
    {
        /**
         * @TODO дублирование кода из модели StudentProfile
         */
        if ($key == 'StudentProfile.medical_files_id') {
            $value = json_decode($value, true);

            if (!is_array($value)) {
                return [];
            }

            return array_map(function ($id) {
                return (int)$id;
            }, $value);
        }

        return parent::castAttribute($key, $value);
    }
}
