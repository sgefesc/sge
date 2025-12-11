<x-mail::message>
Olá {{ $aluno->nome }},
<br><br>
{{ $mensagem }}
<br>
Você pode entregar um novo atestado diretamente na secretara ou acessando seu perfil no link abaixo:
<x-mail::button :url="'https://fesc.app.br/perfil'">
Perfil Fesc
</x-mail::button>

Atenciosamente,<br>
<strong>FESC</strong>
</x-mail::message>

