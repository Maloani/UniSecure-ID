
import cv2
import numpy as np
import os

# Initialisation du modèle LBPH
recognizer = cv2.face.LBPHFaceRecognizer_create()

# Détecteur de visages
face_detector = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")

# Chemin du dataset
dataset_path = 'dataset'

def get_images_and_labels(path):
    face_samples = []
    ids = []

    for folder in os.listdir(path):
        if not folder.isdigit():
            continue
        id = int(folder)
        folder_path = os.path.join(path, folder)

        for image_name in os.listdir(folder_path):
            image_path = os.path.join(folder_path, image_name)
            gray_img = cv2.imread(image_path, cv2.IMREAD_GRAYSCALE)

            faces = face_detector.detectMultiScale(gray_img)
            for (x, y, w, h) in faces:
                face_samples.append(gray_img[y:y+h, x:x+w])
                ids.append(id)

    return face_samples, ids

print("\n[INFO] Entraînement du modèle en cours. Veuillez patienter...")

faces, ids = get_images_and_labels(dataset_path)
recognizer.train(faces, np.array(ids))

# Créer le dossier trainer si nécessaire
os.makedirs('trainer', exist_ok=True)

# Sauvegarder le modèle entraîné
recognizer.save('trainer/trainer.yml')

print(f"\n[INFO] Entraînement terminé. {len(np.unique(ids))} personne(s) enregistrée(s).")
