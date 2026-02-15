import TelegramController from './TelegramController'
import Settings from './Settings'
import AuthController from './AuthController'
import AdminController from './AdminController'

const Controllers = {
    TelegramController: Object.assign(TelegramController, TelegramController),
    Settings: Object.assign(Settings, Settings),
    AuthController: Object.assign(AuthController, AuthController),
    AdminController: Object.assign(AdminController, AdminController),
}

export default Controllers