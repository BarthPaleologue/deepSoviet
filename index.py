
# coding: utf-8

# In[2]:


import numpy as np #module pour faire des maths et manipuler des tenseurs
import itertools #truc random pour faire marcher les graphs

import keras #le module de deep learning
from keras import backend as K #support de Keras (Tensorflow)
from keras.models import Sequential, load_model, Model # de quoi créer un modèle (et en charger un, au besoin)

from keras.layers.core import Dense, Flatten # des couches de neurones simples
from keras.layers.normalization import BatchNormalization # du preprocessing d'image
from keras.layers.convolutional import * # les résaux convolutifs pour l'analyse d'image

from keras.applications import imagenet_utils # du preprocessing

import tensorflowjs as tfjs # tensorflow js (conversion à la fin pour le web)

from keras.optimizers import Adam # sous programme responsable de la backpropagation
from keras.metrics import categorical_crossentropy, sparse_categorical_crossentropy, binary_crossentropy #différente fonctions de coûts
from keras.preprocessing.image import ImageDataGenerator # du preprocessing

from matplotlib import pyplot as plt # des graphiques
get_ipython().run_line_magic('matplotlib', 'inline')
from sklearn.metrics import confusion_matrix # des graphiques

print("MODULES : IMPORTATION COMPLETE")


# In[3]:


def plot_confusion_matrix(cm, classes,
                          normalize=False,
                          title='Confusion matrix',
                          cmap=plt.cm.Blues):
    """
    This function prints and plots the confusion matrix.
    Normalization can be applied by setting `normalize=True`.
    """
    if normalize:
        cm = cm.astype('float') / cm.sum(axis=1)[:, np.newaxis]
        print("Normalized confusion matrix")
    else:
        print('Confusion matrix, without normalization')

    print(cm)

    plt.imshow(cm, interpolation='nearest', cmap=cmap)
    plt.title(title)
    plt.colorbar()
    tick_marks = np.arange(len(classes))
    plt.xticks(tick_marks, classes, rotation=45)
    plt.yticks(tick_marks, classes)

    fmt = '.2f' if normalize else 'd'
    thresh = cm.max() / 2.
    for i, j in itertools.product(range(cm.shape[0]), range(cm.shape[1])):
        plt.text(j, i, format(cm[i, j], fmt),
                 horizontalalignment="center",
                 color="white" if cm[i, j] > thresh else "black")

    plt.tight_layout()
    plt.ylabel('True label')
    plt.xlabel('Predicted label')


# In[4]:


def plots(ims,figsize=(12,6),rows=1,interp=False,titles=None):
    if type(ims[0]) is np.ndarray:
        ims = np.array(ims).astype(np.uint8)
        if(ims.shape[-1] != 3):
            ims = ims.transpose((0,2,3,1))
            
    f = plt.figure(figsize=figsize)
    cols = len(ims)//rows if len(ims)%2 == 0 else len(ims)// rows+1
    for i in range(len(ims)):
        sp = f.add_subplot(rows,cols,i+1)
        sp.axis("Off")
        if titles is not None:
            sp.set_title(titles[i],fontsize=16)
        plt.imshow(ims[i],interpolation=None if interp else "none")


# In[5]:


mobile = keras.applications.mobilenet.MobileNet() # on appelle mobileNet
mobile.summary() # on regarde son architecture
print("MOBILENET : IMPORTATION COMPLETE")


# In[6]:


x = mobile.layers[-6].output # on récupère les six dernières couches
predictions = Dense(2,activation="softmax")(x) # on crée une couche de sortie


# In[7]:


model = Model(inputs=mobile.inputs,outputs=predictions) #on recrée le mobileNet avec les nouvelles couches de sortie


# In[8]:


for layer in model.layers[:-23]: # on freeze les poids et les biais de toutes les couches sauf les 5 dernières (gain de temps et de perfs)
    layer.trainable = False
print("REDEFINITION DE L'ARCHITECTURE MOBILENET : COMPLETE")


# In[9]:


model.compile(Adam(lr=.0001),loss="categorical_crossentropy",metrics=["accuracy"]) #préparation du modèle à l'entraînement
print("COMPILATION : COMPLETE")


# In[22]:


train_path = "./train" #chemin vers l'entraînement
valid_path = "./valid" #chemin vers la validation
test_path = "./test" #chemin vers le test

#preprocessing
train_batches = ImageDataGenerator(preprocessing_function=keras.applications.mobilenet.preprocess_input).flow_from_directory(train_path,target_size=(224,224),classes=["Communist","Other"],batch_size=10)
valid_batches = ImageDataGenerator(preprocessing_function=keras.applications.mobilenet.preprocess_input).flow_from_directory(valid_path,target_size=(224,224),classes=["Communist","Other"],batch_size=5)
test_batches = ImageDataGenerator(preprocessing_function=keras.applications.mobilenet.preprocess_input).flow_from_directory(test_path,target_size=(224,224),classes=["Communist","Other"],batch_size=10, shuffle=False)


# In[13]:


print("DEBUT DE L'ENTRAINEMENT...")
model.fit_generator(train_batches,steps_per_epoch=43,validation_data=valid_batches,validation_steps=6,epochs=7,verbose=1) #entraînement


# In[14]:


print("ENTRAINEMENT : COMPLETE")
test_imgs, test_labels = next(test_batches) #affichage des images de test
plots(test_imgs, titles=test_labels)


# In[15]:


predictions = model.predict_generator(test_batches,steps=1,verbose=2) #test
print("PREDICTION SUR LE TESTSET :")
print(np.round(np.multiply(predictions,100)))


# In[16]:


test_labels = test_labels[:,0]
cm = confusion_matrix(test_labels,np.round(predictions[:,0]))
cm_plot_labels = ["Communist","Other"]
plot_confusion_matrix(cm,cm_plot_labels,title="Confusion Matrix") #matrice de confusion pour évaluer l'IA


# In[17]:


model_json = model.to_json()
with open("model.json","w") as json_file:
    json_file.write(model_json)

model.save("deepSoviet.h5") #modèle keras


# In[18]:


tfjs.converters.save_keras_model(model, "./TFJS") #vers tensorflowjs
print("FIN DU PROGRAMME")

