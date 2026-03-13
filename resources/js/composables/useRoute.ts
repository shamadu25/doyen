/**
 * Composable to prefix all URLs with the application base path.
 * This handles XAMPP subdirectory deployments where the app lives at
 * /garage/garage/public instead of the domain root.
 */
import { usePage } from '@inertiajs/vue3'

export function useRoute() {
    const page = usePage()
    const basePath = (page.props.appBasePath as string) || ''

    function route(path: string): string {
        if (!path.startsWith('/')) return path
        return basePath + path
    }

    return { route, basePath }
}
