# Guide de Soumission sur Packagist

Packagist est le dépôt principal de packages Composer. Une fois soumis, ton module apparaîtra dans les recherches Composer et sera installable directement via `composer require elektrorl/metatag_ai_generate`.

## Étape 1 : Créer un compte Packagist

1. Va sur https://packagist.org
2. Clique sur **"Sign in with GitHub"** (en haut à droite)
3. Autorise Packagist à accéder à ton compte GitHub
4. Tu seras redirigé vers ton profil Packagist

## Étape 2 : Soumettre le Package

1. Une fois connecté, clique sur **"Submit"** dans le menu du haut
2. Colle l'URL de ton repo GitHub :
   ```
   https://github.com/elektrorl/metatag_ai_generate
   ```
3. Clique sur **"Check"**
4. Packagist va analyser ton `composer.json` et afficher un aperçu
5. Si tout est correct, clique sur **"Submit"**

## Étape 3 : Configurer l'Auto-Update (Important!)

Par défaut, Packagist ne se met à jour qu'une fois par jour. Pour des mises à jour instantanées lors de nouveaux commits/releases :

### Option A : GitHub Service Hook (Recommandé)

1. Va sur la page de ton package : https://packagist.org/packages/elektrorl/metatag_ai_generate
2. Clique sur **"Edit"** puis **"Update"**
3. Packagist va automatiquement configurer un webhook GitHub
4. Vérifie dans **Settings** → **Webhooks** de ton repo GitHub qu'un webhook Packagist existe

### Option B : Configuration Manuelle du Webhook

Si l'auto-configuration ne fonctionne pas :

1. Va sur GitHub : https://github.com/elektrorl/metatag_ai_generate/settings/hooks
2. Clique sur **"Add webhook"**
3. Configure :
   - **Payload URL** : `https://packagist.org/api/github?username=elektrorl`
   - **Content type** : `application/json`
   - **Which events** : "Just the push event"
   - Coche "Active"
4. Clique sur **"Add webhook"**

## Étape 4 : Vérifier la Publication

1. Va sur https://packagist.org/packages/elektrorl/metatag_ai_generate
2. Vérifie que :
   - La version **v1.0.0** apparaît
   - La description est correcte
   - Les tags (keywords) sont affichés
   - Le README s'affiche correctement

## Installation par les Utilisateurs

Une fois publié, les utilisateurs pourront installer ton module avec :

```bash
composer require elektrorl/metatag_ai_generate
```

## Mises à Jour Futures

Avec le webhook configuré, chaque fois que tu :
- Crées un nouveau tag/release sur GitHub
- Pousses des commits sur la branche main

Packagist se mettra à jour automatiquement en quelques secondes !

## Commandes Utiles

```bash
# Créer une nouvelle release
git tag -a v1.1.0 -m "Release v1.1.0: Bug fixes"
git push origin v1.1.0
gh release create v1.1.0 --title "v1.1.0" --notes "Bug fixes and improvements"

# Packagist se mettra à jour automatiquement !
```

## Troubleshooting

### Le package n'apparaît pas dans les recherches Composer

- Attends quelques minutes (indexation)
- Vérifie que ton `composer.json` est valide
- Force une mise à jour manuelle sur packagist.org

### Les nouvelles versions n'apparaissent pas

- Vérifie que le webhook GitHub est actif
- Force une mise à jour manuelle sur packagist.org → "Force Update"

### Le badge "downloads" affiche 0

- C'est normal au début ! Il se mettra à jour dès que quelqu'un installera le package

---

**Prêt ?** Va sur https://packagist.org et soumets ton package ! 🚀
