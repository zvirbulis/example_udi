<?php


namespace App\Udi\Customization\Models;

/**
 * App\Udi\Customization\Models\User
 *
 * @property int $id
 * @property string $active
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $personal_phone
 * @property string|null $personal_address
 * @property string|null $personal_birthdate
 * @property string|null $last_login
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $group_id
 * @property mixed|null $settings
 * @property int|null $personal_photo_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Announcement[] $announcements
 * @property-read \App\Models\UserGroup|null $group
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messenger\Message[] $messages
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messenger\Participant[] $participants
 * @property-read \App\Models\File|null $personalPhoto
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SocialAccount[] $socialAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messenger\Thread[] $threads
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User wherePersonalAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User wherePersonalBirthdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User wherePersonalPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User wherePersonalPhotoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Udi\Customization\Models\User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Integration[] $integrations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserIntegration[] $userIntegrations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MagicLink[] $magicLinks
 */
class User extends \App\Models\Users\User
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->casts['User.settings'] = 'settings';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    protected function castAttribute($key, $value)
    {
        if ($key == 'User.settings') {
            $value = $value ?: '';
            return $this->getSettingsAttribute($value)->toJson(JSON_PRETTY_PRINT);
        } elseif ($key == 'group_id') {
            return $this->getAttribute('User.group_id');
        }
        return parent::castAttribute($key, $value);
    }
}
