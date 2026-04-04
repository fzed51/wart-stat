import { useState } from 'react';
import './ComponentsShowcase.css';
import {
  Alert,
  Badge,
  Button,
  ButtonGroup,
  Card,
  EmptyState,
  Input,
  PageHeader,
  Select,
  Separator,
  Table,
  TableHead,
  TableBody,
  TableRow,
  TableCell,
  Terminal,
  TerminalLine,
  Textarea,
} from '../components/common';

/* ── Types ────────────────────────────────────────────────────────────────── */

interface SectionDef {
  id: string;
  label: string;
  group: string;
}

const SECTIONS: SectionDef[] = [
  { id: 'button',     label: 'Button',     group: 'Actions' },
  { id: 'badge',      label: 'Badge',      group: 'Actions' },
  { id: 'alert',      label: 'Alert',      group: 'Feedback' },
  { id: 'card',       label: 'Card',       group: 'Conteneurs' },
  { id: 'terminal',   label: 'Terminal',   group: 'Conteneurs' },
  { id: 'emptystate',  label: 'EmptyState', group: 'Conteneurs' },
  { id: 'input',      label: 'Input',      group: 'Formulaires' },
  { id: 'textarea',   label: 'Textarea',   group: 'Formulaires' },
  { id: 'select',     label: 'Select',     group: 'Formulaires' },
  { id: 'buttongroup',label: 'ButtonGroup',group: 'Formulaires' },
  { id: 'table',      label: 'Table',      group: 'Données' },
  { id: 'separator',  label: 'Separator',  group: 'Mise en page' },
  { id: 'pageheader', label: 'PageHeader', group: 'Mise en page' },
];

/* ── Sous-composants internes ────────────────────────────────────────────── */

function Section({
  id,
  name,
  desc,
  children,
}: {
  id: string;
  name: string;
  desc: string;
  children: React.ReactNode;
}) {
  return (
    <section className="showcase-section" id={id}>
      <div className="showcase-section__header">
        <h2 className="showcase-section__name">{name}</h2>
        <span className="showcase-section__desc">{desc}</span>
      </div>
      {children}
    </section>
  );
}

function Block({
  label,
  preview,
  code,
  previewMod,
}: {
  label?: string;
  preview: React.ReactNode;
  code: string;
  previewMod?: 'column' | 'grid' | 'form';
}) {
  const previewClass =
    previewMod ? `showcase-preview showcase-preview--${previewMod}` : 'showcase-preview';
  return (
    <div className="showcase-block">
      {label && <div className="showcase-block__label">{label}</div>}
      <div className={previewClass}>{preview}</div>
      <pre className="showcase-code">{code}</pre>
    </div>
  );
}

/* ── Page principale ─────────────────────────────────────────────────────── */

export default function ComponentsShowcase() {
  const [active, setActive] = useState('button');

  const scrollTo = (id: string) => {
    setActive(id);
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  // Grouper les sections par groupe
  const groups = SECTIONS.reduce<Record<string, SectionDef[]>>((acc, s) => {
    (acc[s.group] ??= []).push(s);
    return acc;
  }, {});

  return (
    <div className="page">
      <PageHeader
        title="Composants UI"
        subtitle="Bibliothèque de composants — thème hacker"
      />

      <div className="showcase-layout">
        {/* ── Sidebar ── */}
        <aside className="showcase-sidebar">
          <div className="showcase-sidebar__heading">index</div>
          {Object.entries(groups).map(([group, items]) => (
            <div key={group} className="showcase-sidebar__group">
              <span className="showcase-sidebar__group-label">{group}</span>
              {items.map((s) => (
                <button
                  key={s.id}
                  className={`showcase-sidebar__item${active === s.id ? ' active' : ''}`}
                  onClick={() => scrollTo(s.id)}
                >
                  {s.label}
                </button>
              ))}
            </div>
          ))}
        </aside>

        {/* ── Contenu ── */}
        <div className="showcase-content">

          {/* Button */}
          <Section id="button" name="Button" desc="Déclenche une action">
            <Block
              label="Variantes"
              preview={
                <>
                  <Button>Défaut</Button>
                  <Button variant="primary">Primaire</Button>
                  <Button variant="danger">Danger</Button>
                  <Button variant="ghost">Ghost</Button>
                  <Button disabled>Désactivé</Button>
                </>
              }
              code={`<Button>Défaut</Button>
<Button variant="primary">Primaire</Button>
<Button variant="danger">Danger</Button>
<Button variant="ghost">Ghost</Button>
<Button disabled>Désactivé</Button>`}
            />
          </Section>

          {/* Badge */}
          <Section id="badge" name="Badge" desc="Étiquette de statut inline">
            <Block
              label="Variantes"
              preview={
                <>
                  <Badge variant="success">Victoire</Badge>
                  <Badge variant="error">Défaite</Badge>
                  <Badge variant="warning">Attention</Badge>
                  <Badge variant="info">Info</Badge>
                  <Badge variant="neutral">Neutre</Badge>
                </>
              }
              code={`<Badge variant="success">Victoire</Badge>
<Badge variant="error">Défaite</Badge>
<Badge variant="warning">Attention</Badge>
<Badge variant="info">Info</Badge>
<Badge variant="neutral">Neutre</Badge>`}
            />
          </Section>

          {/* Alert */}
          <Section id="alert" name="Alert" desc="Message de feedback contextuel">
            <Block
              label="Variantes"
              previewMod="column"
              preview={
                <>
                  <Alert variant="success">Mission accomplie — Connexion établie avec succès.</Alert>
                  <Alert variant="error">Erreur critique — Impossible de joindre le serveur distant.</Alert>
                  <Alert variant="warning">Avertissement — Batterie faible. Rechargez votre équipement.</Alert>
                  <Alert variant="info">Information — Mise à jour disponible en version 2.0.4.</Alert>
                </>
              }
              code={`<Alert variant="success">Mission accomplie</Alert>
<Alert variant="error">Erreur critique</Alert>
<Alert variant="warning">Avertissement</Alert>
<Alert variant="info">Information</Alert>`}
            />
          </Section>

          {/* Card */}
          <Section id="card" name="Card" desc="Conteneur visuel avec header optionnel">
            <Block
              label="Variantes"
              previewMod="grid"
              preview={
                <>
                  <Card title="Rapport #042">
                    <p>Contenu de la carte avec informations.</p>
                  </Card>
                  <Card title="Mission Delta" onClick={() => {}}>
                    <p>Carte cliquable — survol actif.</p>
                  </Card>
                  <Card>
                    <p>Carte sans titre.</p>
                  </Card>
                </>
              }
              code={`<Card title="Rapport #042">
  <p>Contenu</p>
</Card>

{/* Cliquable */}
<Card title="Mission Delta" onClick={() => navigate('/...')}>
  <p>Contenu</p>
</Card>`}
            />
          </Section>

          {/* Terminal */}
          <Section id="terminal" name="Terminal" desc="Boîte style terminal ASCII">
            <Block
              previewMod="column"
              preview={
                <Terminal title="SYSTEM STATUS">
                  <TerminalLine>Status: ONLINE</TerminalLine>
                  <TerminalLine>Version: 2.0.4</TerminalLine>
                  <TerminalLine>Uptime: 42 jours, 17:32:11</TerminalLine>
                  <TerminalLine prompt>connexion sécurisée établie_</TerminalLine>
                </Terminal>
              }
              code={`<Terminal title="SYSTEM STATUS">
  <TerminalLine>Status: ONLINE</TerminalLine>
  <TerminalLine>Version: 2.0.4</TerminalLine>
  <TerminalLine prompt>commande_</TerminalLine>
</Terminal>`}
            />
          </Section>

          <Separator label="Formulaires" />

          {/* Input */}
          <Section id="input" name="Input" desc="Champ de saisie avec label et validation">
            <Block
              label="États"
              previewMod="form"
              preview={
                <>
                  <Input label="Nom de code" placeholder="Ex: GHOST_RECON" />
                  <Input label="Commande" prefix="$" placeholder="sudo access --level=5" />
                  <Input
                    label="Token d'accès"
                    placeholder="Entrez le token"
                    error="Token invalide ou expiré"
                  />
                </>
              }
              code={`{/* Basique */}
<Input label="Nom de code" placeholder="Ex: GHOST_RECON" />

{/* Avec prefix */}
<Input label="Commande" prefix="$" placeholder="commande" />

{/* Avec erreur */}
<Input label="Token" error="Token invalide" />`}
            />
          </Section>

          {/* Select */}
          <Section id="select" name="Select" desc="Liste déroulante avec label et validation">
            <Block
              label="États"
              previewMod="form"
              preview={
                <>
                  <Select
                    label="Mode opératoire"
                    value="stealth"
                    onChange={() => {}}
                    options={[
                      { value: 'stealth', label: 'Mode Furtif' },
                      { value: 'assault', label: 'Assaut Direct' },
                      { value: 'recon', label: 'Reconnaissance' },
                    ]}
                  />
                  <Select
                    label="Priorité"
                    value=""
                    onChange={() => {}}
                    placeholder="Sélectionner une priorité"
                    options={[
                      { value: 'low', label: 'Faible' },
                      { value: 'medium', label: 'Moyenne' },
                      { value: 'high', label: 'Haute' },
                    ]}
                    error="Priorité requise"
                  />
                </>
              }
              code={`<Select
  label="Mode opératoire"
  value={value}
  onChange={(v) => setValue(v)}
  options={[
    { value: 'stealth', label: 'Mode Furtif' },
    { value: 'assault', label: 'Assaut Direct' },
  ]}
/>`}
            />
          </Section>

          <Separator label="Mise en page" />

          {/* Separator */}
          <Section id="separator" name="Separator" desc="Séparateur de section">
            <Block
              label="Variantes"
              previewMod="column"
              preview={
                <>
                  <Separator />
                  <Separator label="Section suivante" />
                  <Separator />
                </>
              }
              code={`{/* Simple */}
<Separator />

{/* Avec label */}
<Separator label="Section suivante" />`}
            />
          </Section>

          {/* PageHeader */}
          <Section id="pageheader" name="PageHeader" desc="En-tête standard de page">
            <Block
              previewMod="column"
              preview={
                <PageHeader
                  title="Exemple de Page"
                  subtitle="Voici une description de la page en sous-titre"
                />
              }
              code={`<PageHeader
  title="Nom de la page"
  subtitle="Description courte de la page"
/>`}
            />
          </Section>

          <Separator label="Nouveaux composants" />

          {/* Textarea */}
          <Section id="textarea" name="Textarea" desc="Champ multi-ligne avec compteur">
            <Block
              label="États"
              previewMod="form"
              preview={
                <>
                  <Textarea label="Description" placeholder="Entrez une description..." />
                  <Textarea
                    label="Rapport"
                    placeholder="Contenu du rapport"
                    maxLength={200}
                    showCounter
                    value="Ceci est un exemple avec compteur de caractères. C'est utile po"
                    onChange={() => {}}
                  />
                  <Textarea
                    label="Erreur"
                    placeholder="Erreur de validation"
                    error="Le contenu est requis"
                  />
                </>
              }
              code={`{/* Basique */}
<Textarea label="Description" placeholder="..." />

{/* Avec compteur */}
<Textarea
  label="Rapport"
  maxLength={200}
  showCounter
  value={content}
  onChange={(e) => setContent(e.target.value)}
/>

{/* Avec erreur */}
<Textarea label="Erreur" error="Requis" />`}
            />
          </Section>

          {/* ButtonGroup */}
          <Section id="buttongroup" name="ButtonGroup" desc="Groupe de boutons alignés">
            <Block
              label="Horizontal (défaut)"
              preview={
                <ButtonGroup>
                  <Button variant="ghost">Annuler</Button>
                  <Button variant="primary">Valider</Button>
                </ButtonGroup>
              }
              code={`<ButtonGroup>
  <Button variant="ghost">Annuler</Button>
  <Button variant="primary">Valider</Button>
</ButtonGroup>`}
            />
            <Block
              label="Vertical"
              previewMod="column"
              preview={
                <ButtonGroup vertical>
                  <Button>Action 1</Button>
                  <Button>Action 2</Button>
                  <Button>Action 3</Button>
                </ButtonGroup>
              }
              code={`<ButtonGroup vertical>
  <Button>Action 1</Button>
  <Button>Action 2</Button>
  <Button>Action 3</Button>
</ButtonGroup>`}
            />
          </Section>

          {/* EmptyState */}
          <Section id="emptystate" name="EmptyState" desc="État vide avec message et action">
            <Block
              previewMod="column"
              preview={
                <EmptyState
                  icon="∅"
                  title="Aucun résultat"
                  description="Aucun rapport trouvé pour ces critères de recherche."
                  action={<Button variant="primary">Créer un rapport</Button>}
                />
              }
              code={`<EmptyState
  icon="∅"
  title="Aucun résultat"
  description="Aucun rapport trouvé."
  action={<Button onClick={...}>Créer</Button>}
/>`}
            />
          </Section>

          {/* Table */}
          <Section id="table" name="Table" desc="Tableau de données">
            <Block
              previewMod="column"
              preview={
                <Table>
                  <TableHead>
                    <TableRow>
                      <TableCell isHeader>ID</TableCell>
                      <TableCell isHeader>Pays</TableCell>
                      <TableCell isHeader>Mission</TableCell>
                      <TableCell isHeader align="right">Points</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    <TableRow onClick={() => {}}>
                      <TableCell>#042</TableCell>
                      <TableCell>France</TableCell>
                      <TableCell>Destruction</TableCell>
                      <TableCell align="right">1500</TableCell>
                    </TableRow>
                    <TableRow onClick={() => {}}>
                      <TableCell>#041</TableCell>
                      <TableCell>Allemagne</TableCell>
                      <TableCell>Capture</TableCell>
                      <TableCell align="right">2100</TableCell>
                    </TableRow>
                    <TableRow onClick={() => {}}>
                      <TableCell>#040</TableCell>
                      <TableCell>Russie</TableCell>
                      <TableCell>Survie</TableCell>
                      <TableCell align="right">850</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              }
              code={`<Table>
  <TableHead>
    <TableRow>
      <TableCell isHeader>ID</TableCell>
      <TableCell isHeader>Pays</TableCell>
      <TableCell isHeader align="right">Points</TableCell>
    </TableRow>
  </TableHead>
  <TableBody>
    <TableRow onClick={() => navigate(...)}>  
      <TableCell>#042</TableCell>
      <TableCell>France</TableCell>
      <TableCell align="right">1500</TableCell>
    </TableRow>
  </TableBody>
</Table>`}
            />
          </Section>

        </div>
      </div>
    </div>
  );
}
