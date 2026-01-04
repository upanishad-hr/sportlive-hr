import en from './en.json';
import hr from './hr.json';
import de from './de.json';

export const languages = {
  en: 'English',
  hr: 'Hrvatski',
  de: 'Deutsch'
} as const;

export type Lang = keyof typeof languages;

const translations = { en, hr, de } as const;

export function getLangFromUrl(url: URL): Lang {
  const [, lang] = url.pathname.split('/');
  if (lang in languages) return lang as Lang;
  return 'en';
}

export function useTranslations(lang: Lang) {
  return translations[lang];
}

export function getLocalizedPath(path: string, lang: Lang): string {
  if (lang === 'en') return path;
  return `/${lang}${path}`;
}
