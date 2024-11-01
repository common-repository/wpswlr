import { useCallback, useState } from 'react';
import { toast } from 'react-toastify';
import type { WP_REST_API_Categories } from 'wp-types';
import { HttpService } from '../services/HttpService';
import { tr } from '../i18n';

interface Category {
    id: number;
    name: string;
    description: string;
    level: number;
    children: Category[];
}

let categoriesPromise: Promise<WP_REST_API_Categories> | undefined;

function createCategoryTree(terms: WP_REST_API_Categories, parentCategory = 0, level = 0): Category[] {
    return terms
        .filter((t) => t.parent === parentCategory)
        .map((t) => ({
            id: t.id,
            name: t.name,
            description: t.description,
            level,
            children: createCategoryTree(terms, t.id, level + 1),
        }));
}

function flattenTree(tree: Category[], categories: Category[] = []): Category[] {
    tree.forEach((c) => {
        categories.push({
            ...c,
            children: [],
        });
        if (c.children.length) {
            flattenTree(c.children, categories);
        }
    });

    return categories;
}

export const useCategories = (): [Category[] | null, () => Promise<void>] => {
    const [categories, setCategories] = useState<Category[] | null>(null);

    const load = useCallback(async () => {
        if (!categoriesPromise) {
            categoriesPromise = HttpService.get<WP_REST_API_Categories>(
                `${window.WPSWLR.rest_url}wp/v2/categories`
            ).catch(() => {
                toast.warn(tr.app.categoriesLoadFailed);
                return [];
            });
        }
        const terms = await categoriesPromise;
        terms.sort((a, b) => a.name.localeCompare(b.name));
        setCategories(flattenTree(createCategoryTree(terms)));
    }, []);

    return [categories, load];
};
