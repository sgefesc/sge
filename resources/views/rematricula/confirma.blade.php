<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rematricula realizada com sucesso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <style>
        h1 {margin-top:2rem;
            font-size:14pt;
            font-weight: bold;}
        h5{
            margin-top:2rem;
            text-align: center

            }
        .description{
            margin-top:2rem;
            font-size:12pt;
        }
        .form{
            margin-top:2rem;
        }
        .button{
            margin-top:.1rem;
        }
        .importante{
            color:red;
            font-weight: bold;
        }
    </style>
    <script>
    function termo(id){
        document.forms[0].action = "/rematricula/termo/"+id;
        document.forms[0].submit();
    }
    </script>
 
</head>
<body>
    <div class="container">
        <h1>
            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check-circle-fill" fill="green" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
              </svg>
          Rematrícula(s) realizada(s) com sucesso.</h1>
        <div class="description">
        <p>
        A seguinte matrículas foram geradas:<br>
            @foreach($matriculas as $id)
             Matrícula número: <a href="#" onclick="termo({{$id}});"><strong>{{$id}}</strong></a><br>
            @endforeach
        </p>
             
            
       </div>
       <form  method="post" target="_blank">
       {{ csrf_field() }}
       <input type="hidden" name = "keycode" value = "{{$pessoa}}">
       <form>
       <p>O início das aulas está previsto para a segunda semana de fevereiro,  mas poderá ser alterado, seguindo as determinações do Plano SP e da Prefeitura Municipal de São Carlos, através do Comitê do COVID-19</p>
      
        
   </div>
       

        

    </div>
   
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
</body>

</html>