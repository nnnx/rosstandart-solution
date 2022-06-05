FROM python:3.7.6-buster

RUN pip3 install virtualenv
RUN pip3 install flask
RUN pip3 install pandas
RUN pip3 install numpy
RUN pip3 install tqdm
RUN pip3 install catboost
RUN pip3 install scikit-learn
RUN pip3 install pymupdf
RUN pip3 install var_dump
RUN pip3 install pymorphy2
RUN pip3 install fasttext

WORKDIR /app/python
ENTRYPOINT ["python"]
CMD ["main.py"]