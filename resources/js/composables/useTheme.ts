import { computed, readonly, shallowRef, watch } from 'vue';

type ThemePreference = 'light' | 'dark' | 'system';

const storageKey = 'peopleops.theme';
const preference = shallowRef<ThemePreference>('system');
const systemPrefersDark = shallowRef(false);
let initialized = false;

function applyTheme(): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.toggle('dark', isDark.value);
    document.documentElement.style.colorScheme = isDark.value ? 'dark' : 'light';
}

const isDark = computed(() => preference.value === 'dark' || (preference.value === 'system' && systemPrefersDark.value));

const label = computed(() => (isDark.value ? 'Switch to light mode' : 'Switch to dark mode'));

export function useTheme() {
    function initialize(): void {
        if (initialized || typeof window === 'undefined') {
            return;
        }

        initialized = true;
        const storedPreference = window.localStorage.getItem(storageKey);

        if (storedPreference === 'light' || storedPreference === 'dark' || storedPreference === 'system') {
            preference.value = storedPreference;
        }

        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        systemPrefersDark.value = mediaQuery.matches;
        mediaQuery.addEventListener('change', (event) => {
            systemPrefersDark.value = event.matches;
        });

        watch(
            [preference, systemPrefersDark],
            () => {
                window.localStorage.setItem(storageKey, preference.value);
                applyTheme();
            },
            { immediate: true },
        );
    }

    function toggle(): void {
        preference.value = isDark.value ? 'light' : 'dark';
    }

    function setPreference(nextPreference: ThemePreference): void {
        preference.value = nextPreference;
    }

    return {
        preference: readonly(preference),
        isDark,
        label,
        initialize,
        setPreference,
        toggle,
    };
}
