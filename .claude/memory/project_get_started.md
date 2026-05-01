---
name: Page get-started — demande d'inscription entreprise
description: Structure, champs, et écarts entre le formulaire public /demarrer et la table companies admin
type: project
---

## Page `/demarrer` (`resources/views/get-started.blade.php`)

Formulaire 4 étapes pour qu'une entreprise fasse une demande d'inscription sur CauriShop.

**Étape 1 — Gérant/DG** : nom, prénom, téléphone, pièce d'identité (select), adresse personnelle  
**Étape 2 — Entreprise** : raison sociale, téléphone, date de création, nb employés (select), adresse  
**Étape 3 — Documents** : upload RCCM (oblig.), NIF (oblig.), Statuts (oblig.), CNI gérant (oblig.), Patente (facultatif), Attestation domiciliation (facultatif)  
**Étape 4 — Confirmation** : récap + soumission via `POST /demarrer`

**Route backend** : `routes/web.php` — le handler POST valide les champs texte mais **ne sauvegarde rien en base et ne stocke aucun fichier**.

---

## Table `companies` (migration + Model + admin form)

Champs : `name`, `registration_number`, `email`, `phone`, `address`, `city`, `country`, `credit_limit`, `is_active`

Le formulaire admin (`admin/companies/create.blade.php`) crée directement une entreprise avec ces champs.  
`CreateCompanyRequest` : email et registration_number sont uniques et requis côté admin.

---

## Écarts critiques entre /demarrer et la table companies

| Champ table | Dans /demarrer | Statut |
|---|---|---|
| `name` | ✅ `e_raison` | OK |
| `email` | ❌ absent | **Manquant** |
| `phone` | ✅ `e_tel` | OK |
| `registration_number` | Collecté comme fichier (RCCM/NIF) mais pas comme texte | **Partiel** |
| `address` | ✅ `e_adresse` (champ libre, sans ville/pays) | Partiel |
| `city` | ❌ absent | **Manquant** |
| `country` | ❌ absent | **Manquant** |
| `credit_limit` | ❌ absent (logique, admin-only) | Normal |
| `is_active` | ❌ absent (logique, admin-only) | Normal |

**Données collectées dans /demarrer sans colonne en base** :
- Infos gérant (nom, prénom, pièce d'identité, adresse personnelle)
- Date de création de l'entreprise
- Nombre d'employés
- Fichiers uploadés (RCCM, NIF, Statuts, CNI, etc.)

**Problème structurel** : il n'existe pas de table `registration_requests` pour stocker les demandes en attente. Le flux prévu (demande → validation admin → création compte) n'a pas de modèle de données dédié.

**Why:** L'utilisateur souhaite aligner la page publique /demarrer avec la création d'entreprise côté admin pour implémenter le flux complet.  
**How to apply:** Toute modification doit tenir compte de ces deux entrées (formulaire public ET création admin) pour être cohérente.
