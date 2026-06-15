<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Identifiants de connexion</title>
</head>
<body>
    <h2>Bienvenue sur {{ config('app.name') }}</h2>
    
    <p>Bonjour {{ $superviseur->nom_user }},</p>
    
    <p>Vous avez été créé en tant que <strong>superviseur</strong> sur la plateforme <strong>{{ config('app.name') }}</strong>.</p>
    
    <p>Voici vos identifiants de connexion :</p>
    
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="padding: 10px; border: 1px solid #ccc;"><strong>Email :</strong></td>
            <td style="padding: 10px; border: 1px solid #ccc;">{{ $superviseur->email }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #ccc;"><strong>Mot de passe :</strong></td>
            <td style="padding: 10px; border: 1px solid #ccc;"><code>{{ $password }}</code></td>
        </tr>
    </table>
    
    <p style="margin-top: 20px;">
        <a href="{{ env('APP_URL') }}" style="display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Se connecter</a>
    </p>
    
    <p style="font-size: 12px; color: #666; margin-top: 30px;">
        <strong>Remarque importante :</strong> Nous vous recommandons de changer votre mot de passe lors de votre première connexion.
    </p>
    
    <p style="margin-top: 20px;">
        Pour toute assistance, n'hésitez pas à contacter l'équipe d'administration.
    </p>
    
    <br>
    <p>Cordialement,<br>
    {{ config('app.name') }}</p>
</body>
</html>
