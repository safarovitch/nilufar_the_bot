import TelegramController from './TelegramController'
import Settings from './Settings'
import AuthController from './AuthController'

const Controllers = {
    TelegramController: Object.assign(TelegramController, TelegramController),
    Settings: Object.assign(Settings, Settings),
    AuthController: Object.assign(AuthController, AuthController),
}

export default Controllers