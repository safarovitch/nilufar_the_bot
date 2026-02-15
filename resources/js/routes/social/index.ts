import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\AuthController::auth
* @see app/Http/Controllers/AuthController.php:21
* @route '/auth/{provider}'
*/
export const auth = (args: { provider: string | number } | [provider: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: auth.url(args, options),
    method: 'get',
})

auth.definition = {
    methods: ["get","head"],
    url: '/auth/{provider}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AuthController::auth
* @see app/Http/Controllers/AuthController.php:21
* @route '/auth/{provider}'
*/
auth.url = (args: { provider: string | number } | [provider: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { provider: args }
    }

    if (Array.isArray(args)) {
        args = {
            provider: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        provider: args.provider,
    }

    return auth.definition.url
            .replace('{provider}', parsedArgs.provider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::auth
* @see app/Http/Controllers/AuthController.php:21
* @route '/auth/{provider}'
*/
auth.get = (args: { provider: string | number } | [provider: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: auth.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AuthController::auth
* @see app/Http/Controllers/AuthController.php:21
* @route '/auth/{provider}'
*/
auth.head = (args: { provider: string | number } | [provider: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: auth.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AuthController::auth
* @see app/Http/Controllers/AuthController.php:21
* @route '/auth/{provider}'
*/
const authForm = (args: { provider: string | number } | [provider: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: auth.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AuthController::auth
* @see app/Http/Controllers/AuthController.php:21
* @route '/auth/{provider}'
*/
authForm.get = (args: { provider: string | number } | [provider: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: auth.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AuthController::auth
* @see app/Http/Controllers/AuthController.php:21
* @route '/auth/{provider}'
*/
authForm.head = (args: { provider: string | number } | [provider: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: auth.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

auth.form = authForm

const social = {
    auth: Object.assign(auth, auth),
}

export default social