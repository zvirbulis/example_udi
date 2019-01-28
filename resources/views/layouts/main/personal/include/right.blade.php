<div class="lk-right">
    @if(Gate::check('read', 'AdminInstruments') && !$userProfile->isAuthUserProfile())
        <div class="admin-panel-in-profile menu-lk hide-for-mobile" style="display: none;">
            <h3>Інструменти адміністратора</h3>
            <hr/>
            <ul>
                <li>
                    <a href="{{ $userProfile->getUserUdiUrl() }}">
                        <i><img src="{{ asset('/images/lk-ico-2.png') }}" alt=" " /></i>
                        <span>Картка користувача</span>
                    </a>
                </li>
            </ul>
        </div>
    @endif
</div>
