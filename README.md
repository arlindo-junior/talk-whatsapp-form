# Talk WhatsApp Form + Kanban

Um plugin WordPress que captura leads através de um formulário WhatsApp e gerencia o status dos leads em um sistema Kanban.

## 🚀 Funcionalidades

### Frontend
- **Botão flutuante WhatsApp**: Botão fixo no canto inferior direito de todas as páginas
- **Popup de formulário**: Modal elegante para captura de leads
- **Validação de telefone**: Máscara automática para números brasileiros
- **Feedback visual**: Confirmação de envio com animação

### Admin/Backend
- **Custom Post Type**: Gerenciamento de leads como posts customizados
- **Sistema Kanban**: Interface visual para gestão de status dos leads
- **Drag & Drop**: Arraste leads entre colunas para alterar status
- **Colunas de Status**:
  - **A Enviar**: Leads recém-cadastrados
  - **Enviado**: Leads já processados

### Recursos Técnicos
- **AJAX**: Envio de formulário sem reload da página
- **Nonce Security**: Proteção contra ataques CSRF
- **Sanitização**: Todos os dados são sanitizados antes do armazenamento
- **Responsivo**: Interface adaptável para desktop e mobile

## 📦 Instalação

### Método 1: Upload Manual
1. Faça download dos arquivos do plugin
2. Acesse o painel WordPress: `Plugins > Adicionar novo > Enviar plugin`
3. Selecione o arquivo ZIP do plugin
4. Clique em "Instalar agora"
5. Ative o plugin

### Método 2: FTP
1. Extraia os arquivos do plugin
2. Envie a pasta `talk-whatsapp-form` para `/wp-content/plugins/`
3. No painel WordPress, vá em `Plugins > Plugins instalados`
4. Ative o plugin "Talk WhatsApp Form + Kanban"


## 🔧 Como Usar

### 1. Configuração Inicial
Após ativar o plugin, você verá:
- Um novo menu "Leads da Talk" no admin
- O botão flutuante aparecerá automaticamente no frontend

### 2. Captura de Leads (Frontend)
1. Os visitantes verão um botão verde "📩 Receba esta talk pelo WhatsApp"
2. Ao clicar, abrirá um popup com formulário
3. Campos obrigatórios: Nome e Telefone
4. O telefone é automaticamente formatado como (00) 00000-0000
5. Após envio, exibe confirmação de sucesso

### 3. Gerenciamento de Leads (Admin)

#### Visualização Lista
- Acesse `Leads da Talk` no menu lateral
- Veja todos os leads cadastrados
- Edite informações individuais se necessário

#### Sistema Kanban
- Clique em `📌 Kanban` no submenu
- Visualize leads organizados por status
- **Arraste e solte** leads entre colunas para alterar status
- Status disponíveis:
  - **A Enviar**: Leads aguardando processamento
  - **Enviado**: Leads já processados

### 4. Fluxo de Trabalho Recomendado
1. Leads são criados automaticamente com status "A Enviar"
2. Processe os leads (envie conteúdo via WhatsApp)
3. Mova o lead para coluna "Enviado" no Kanban
4. Mantenha controle visual do pipeline de leads


## 🔒 Segurança

O plugin implementa:
- **Nonce verification**: Proteção contra ataques CSRF
- **Sanitização de dados**: Todos os inputs são sanitizados
- **Verificação de permissões**: Acesso restrito ao admin
- **Validação AJAX**: Verificação de nonce em requisições

## 📋 Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- jQuery (incluído no WordPress)
- jQuery UI Sortable (carregado automaticamente)

## 🐛 Troubleshooting

### Botão não aparece no frontend
- Verifique se o plugin está ativo
- Confirme se o tema usa `wp_footer()` hook

### Kanban não funciona
- Verifique se os arquivos `kanban.css` e `kanban.js` existem
- Confirme permissões de arquivo no servidor

### Leads não são salvos
- Verifique console do navegador para erros JavaScript
- Confirme se AJAX está funcionando corretamente

