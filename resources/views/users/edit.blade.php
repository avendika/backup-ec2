<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar-selection {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .avatar-option {
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 5px;
        }
        .avatar-option.selected {
            border-color: #0d6efd;
        }
        .avatar-option img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .custom-avatar-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Edit User</h1>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to List</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $user->username) }}" required maxlength="30">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Avatar</label>
                        <div class="avatar-selection" id="avatarSelection">
                            <!-- Default avatars will be loaded here via JavaScript -->
                        </div>
                        <input type="hidden" name="avatar" id="selectedAvatar" value="{{ old('avatar', $user->avatar) }}">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="useCustomAvatar" name="custom_avatar">
                            <label class="form-check-label" for="useCustomAvatar">
                                Upload New Custom Avatar
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="customAvatarUpload" style="display: none;">
                        <label for="avatar_file" class="form-label">Upload Avatar</label>
                        <input type="file" class="form-control" id="avatar_file" name="avatar_file" accept="image/*">
                        <div class="mt-2">
                            <img id="avatarPreview" class="custom-avatar-preview" style="display: none;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <input type="number" class="form-control" id="level" name="level" value="{{ old('level', $user->level) }}" min="1">
                    </div>

                    <div class="mb-3">
                        <label for="score" class="form-label">Score</label>
                        <input type="number" class="form-control" id="score" name="score" value="{{ old('score', $user->score) }}" min="0">
                    </div>

                    <button type="submit" class="btn btn-primary">Update User</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Change Password</h5>
                <form action="{{ route('users.change-password', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load available avatars
        document.addEventListener('DOMContentLoaded', function() {
            loadAvatars();

            document.getElementById('useCustomAvatar').addEventListener('change', function() {
                document.getElementById('customAvatarUpload').style.display = this.checked ? 'block' : 'none';
            });

            document.getElementById('avatar_file').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('avatarPreview');
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        function loadAvatars() {
            const defaultAvatars = [
                'assets/avatars/avatar1.png',
                'assets/avatars/avatar2.png',
                'assets/avatars/avatar3.png',
                'assets/avatars/avatar4.png',
                'assets/avatars/default.png'
            ];

            const currentAvatar = document.getElementById('selectedAvatar').value;
            const selection = document.getElementById('avatarSelection');
            
            defaultAvatars.forEach(avatar => {
                const option = document.createElement('div');
                option.className = 'avatar-option';
                if (avatar === currentAvatar) {
                    option.classList.add('selected');
                }
                option.onclick = () => selectAvatar(avatar, option);
                option.innerHTML = `<img src="${avatar}" alt="Avatar">`;
                selection.appendChild(option);
            });
        }

        function selectAvatar(avatar, element) {
            const options = document.querySelectorAll('.avatar-option');
            options.forEach(option => option.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('selectedAvatar').value = avatar;
        }
    </script>
</body>
</html>